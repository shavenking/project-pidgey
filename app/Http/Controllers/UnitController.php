<?php

namespace App\Http\Controllers;

use App\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function list(Unit $unit)
    {
        $units = $unit->get();

        return response()->json(['data' => $units]);
    }
}
