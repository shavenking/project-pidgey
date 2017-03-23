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
}
