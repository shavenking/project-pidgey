<?php

namespace App\Http\Controllers;

use App\User;
use JWTAuth;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function createUser(Request $request)
    {
        // validate if email already in used
        if (User::where('email', $request->email)->exists()) {
            return response()->json([], 409);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password]);

        return response()->json(['data' => compact('token')], 201);
    }

    public function createToken(Request $request)
    {
        try {
            $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password]);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }

        if (!$token) {
            return response()->json([], 401);
        }

        return response()->json(['data' => compact('token')], 201);
    }
}
