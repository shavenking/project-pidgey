<?php

namespace App\Http\Controllers;

use App\Work;
use JWTAuth;
use Illuminate\Http\Request;

class WorkController extends Controller
{
    public function list(Work $work)
    {
        return response()->json([
            'data' => $work->with('engineeringType', 'unit')->get()
        ]);
    }

    public function create(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $work = $user->works()->create([
            'name' => $request->name,
            'unit_id' => $request->unit_id,
            'unit_price' => "0.00",
            'engineering_type_id' => $request->engineering_type_id
        ])->load('engineeringType', 'unit');

        return response()->json(['data' => $work], 201);
    }

    public function delete(Work $work)
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($user->id !== $work->user_id) {
            return response()->json([], 403);
        }

        $work->workItems()->detach();
        $work->delete();

        return response()->json([], 204);
    }
}
