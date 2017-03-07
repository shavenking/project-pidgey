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
}
