<?php

namespace App\Http\Controllers;

use App\{
    Project,
    ProjectWork
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
}
