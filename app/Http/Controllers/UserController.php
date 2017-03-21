<?php

namespace App\Http\Controllers;

use App\User;
use JWTAuth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function profile()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exceptions $e) {
            return response()->json([], 401);
        }

        if (!$user) {
            return response()->json([], 404);
        }

        return response()->json(['data' => $user]);
    }
}
