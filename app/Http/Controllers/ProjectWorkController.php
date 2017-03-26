<?php

namespace App\Http\Controllers;

use App\{
    Project,
    Work
};
use JWTAuth;
use Illuminate\Http\Request;

class ProjectWorkController extends Controller
{
    public function list(Project $project)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // currently only project owner can access
        if ($project->user_id !== $user->id) {
            return response()->json([], 403);
        }

        $works = $project->works()->with('engineeringType')->get()->map(function ($work) {
            $base = array_only($work->toArray(), [
                'id', 'name', 'amount', 'unit_price', 'engineering_type_id', 'project_id'
            ]);

            return array_merge($base, [
                'engineering_type_main_title' => $work->engineeringType->main_title,
                'engineering_type_detailing_title' => $work->engineeringType->detailing_title
            ]);
        });

        return response()->json(['data' => $works]);
    }

    public function create(Request $request, Project $project)
    {
        $user = JWTAuth::parseToken()->authenticate();

        // currently only project owner can access
        if ($project->user_id !== $user->id) {
            return response()->json([], 403);
        }

        if (!$request->has('work_id') && !$request->has('name')) {
            return response()->json([], 400);
        }

        if ($request->has('work_id')) {
            $stdWork = Work::with('workItems')->find($request->work_id);
        }

        $work = $project->works()->create([
            'name' => isset($stdWork) ? $stdWork->name : $request->name,
            'amount' => $request->amount,
            'unit_price' => isset($stdWork) ? $stdWork->unit_price : '0.00',
            'engineering_type_id' => isset($stdWork) ? $stdWork->engineering_type_id : $request->engineering_type_id
        ]);

        $data = array_merge(
            array_only($work->toArray(), ['id', 'name', 'amount', 'unit_price', 'engineering_type_id', 'project_id']),
            [
                'engineering_type_main_title' => $work->engineeringType->main_title,
                'engineering_type_detailing_title' => $work->engineeringType->detailing_title
            ]
        );

        // TODO: 標準工料項目也得自動加入

        return response()->json(compact('data'), 201);
    }
}
