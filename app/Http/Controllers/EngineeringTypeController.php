<?php

namespace App\Http\Controllers;

use App\EngineeringType;
use Illuminate\Http\Request;

class EngineeringTypeController extends Controller
{
    public function list(EngineeringType $engineeringType)
    {
        $engineeringTypes = $engineeringType->get();

        return response()->json(['data' => $engineeringTypes]);
    }
}
