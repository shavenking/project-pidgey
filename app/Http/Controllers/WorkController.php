<?php

namespace App\Http\Controllers;

use App\Work;
use Illuminate\Http\Request;

class WorkController extends Controller
{
    public function list(Work $work)
    {
        return response()->json([
            'data' => $work->with('engineeringType')->get()
        ]);
    }

    public function create(Request $request, Work $work)
    {
        $work = $work->create([
            'name' => $request->name,
            'amount' => $request->amount,
            'unit_price' => 0,
            'engineering_type_id' => $request->engineering_type_id
        ])->load('engineeringType');

        return response()->json(['data' => $work], 201);
    }

    public function delete(Work $work)
    {
        $work->workItems()->detach();
        $work->delete();

        return response()->json([], 204);
    }
}
