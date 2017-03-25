<?php

namespace App\Http\Controllers;

use App\Project;
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
}
