<?php

namespace App\Http\Controllers;

use App\CostType;
use Illuminate\Http\Request;

class CostTypeController extends Controller
{
    public function list(CostType $costType)
    {
        $costTypes = $costType->get();

        return response()->json(['data' => $costTypes]);
    }
}
