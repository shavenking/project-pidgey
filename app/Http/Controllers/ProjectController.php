<?php

namespace App\Http\Controllers;

use App\Project;
use JWTAuth;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function list()
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json(['data' => $user->projects]);
    }

    public function create(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $project = $user->projects()->create(['name' => $request->name]);

        return response()->json([
            'data' => $project->toArray()
        ], 201);
    }

    public function delete(Project $project)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->id !== $project->user_id) {
            return response()->json([], 403);
        }

        $project->delete();

        return response()->json([], 204);
    }
}
