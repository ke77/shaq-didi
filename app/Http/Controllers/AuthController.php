<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function __construct(private AuthService $auth) {}

    // register
    public function register(RegisterRequest $request) 
    {
        ['user' => $user, 'token' => $token] = $this->auth->register($request->validated());

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }



    // handle sign in
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        ['user' => $user, 'token' => $token] = $this->auth->login(
            $credentials['email'],
            $credentials['password']
        );

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }



    // fetch current user profile (/me)
    public function me(Request $request)
    {
        return response()->json($request->user());
    }


    // handle logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }
}
