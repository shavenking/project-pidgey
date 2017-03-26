<?php

namespace App\Http\Controllers;

use App\{
    Project,
    ProjectWork,
    WorkItem
};
use JWTAuth;
use Illuminate\Http\Request;

class ProjectWorkItemController extends Controller
{
    public function list(Project $project, ProjectWork $work)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->id !== $project->user_id) { return response()->json([], 403); }
        if ($project->id !== $work->project_id) { return response()->json([], 400); }

        $workItems = $work->workItems()->with('unit', 'costType')->get()->map(function ($workItem) use ($work) {
            $base = array_only($workItem->toArray(), [
                'id', 'project_id', 'unit_id', 'cost_type_id', 'name'
            ]);

            return array_merge($base, [
                'project_work_id' => $work->id,
                'amount' => $workItem->pivot->amount,
                'unit_price' => $workItem->pivot->unit_price,
                'unit_name' => $workItem->unit->name,
                'cost_type_name' => $workItem->costType->name
            ]);
        });

        return response()->json(['data' => $workItems]);
    }

    public function listWithoutWork(Project $project)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->id !== $project->user_id) { return response()->json([], 403); }

        $workItems = $project->workItems()->with('unit', 'costType')->get()->map(function ($workItem) {
            $base = array_only($workItem->toArray(), [
                'id', 'project_id', 'unit_id', 'cost_type_id', 'name'
            ]);

            return array_merge($base, [
                'unit_name' => $workItem->unit->name,
                'cost_type_name' => $workItem->costType->name
            ]);
        });

        return response()->json(['data' => $workItems]);
    }

    public function create(Request $request, Project $project, ProjectWork $work)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->id !== $project->user_id) { return response()->json([], 403); }
        if ($project->id !== $work->project_id) { return response()->json([], 400); }

        \DB::beginTransaction();

        if ($request->has('project_work_item_id')) {
            $workItem = $project->workItems()->find($request->project_work_item_id);

            $work->workItems()->attach($workItem, [
                'amount' => $request->amount,
                'unit_price' => $request->unit_price
            ]);

            $workItem = $work->workItems()->with('unit', 'costType')->find($workItem->id);
        } else if ($request->has('work_item_id')) {
            $workItem = WorkItem::with('unit', 'costType')->find($request->work_item_id);

            $workItem = $work->workItems()->create([
                'project_id' => $project->id,
                'unit_id' => $workItem->unit->id,
                'cost_type_id' => $workItem->costType->id,
                'name' => $workItem->name
            ], ['amount' => $request->amount, 'unit_price' => $request->unit_price]);

            $workItem = $work->workItems()->with('unit', 'costType')->find($workItem->id);
        } else if ($request->has('unit_id', 'cost_type_id', 'name')) {
            $workItem = $work->workItems()->create([
                'project_id' => $project->id,
                'unit_id' => $request->unit_id,
                'cost_type_id' => $request->cost_type_id,
                'name' => $request->name
            ], ['amount' => $request->amount, 'unit_price' => $request->unit_price]);

            $workItem = $work->workItems()->with('unit', 'costType')->find($workItem->id);
        }

        // 工作項目的單價需跟著調整
        $work->update([
            'unit_price' => bcadd(
                $work->unit_price,
                bcmul($workItem->pivot->amount, $workItem->pivot->unit_price, 2),
                2
            )
        ]);

        \DB::commit();

        $data = array_merge(
            array_only($workItem->toArray(), ['id', 'project_work_item_id', 'unit_id', 'cost_type_id', 'name']),
            [
                'amount' => $workItem->pivot->amount,
                'unit_price' => $workItem->pivot->unit_price,
                'unit_name' => $workItem->unit->name,
                'cost_type_name' => $workItem->costType->name
            ]
        );

        return response()->json(compact('data'), 201);
    }
}
