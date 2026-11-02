<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    // register
    public function register(Request $request) 
    {
        $user_data = $request->validate([
            'role' => 'required|string|in:permanent_staff,national_service',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone_number' => 'required|string|unique:users,phone_number',
        ]);


        $user = User::create([
            'role' => $user_data['role'],
            'name' => $user_data['name'],
            'email' => $user_data['email'],
            'phone_number' => $user_data['phone_number'],
            'password' => $user_data['password'],
        ]);


        // issue a token to registered user
        $token = $user->createToken('shaq_didi_token')->plainTextToken;
        
        return Response()->json(
            [
                'message' => 'User registered successfully.',
                'user' => $user,
                'token' => $token
        ], 201);
    }



    // handle sign in
    public function login(Request $request)
    {
        $user_data = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string', 
        ]);


        // find user
        $user = User::where('email', $user_data['email'])->first();

        if (!$user || !Hash::check($user_data['password'], $user->password)) {
            return Response()->json([
                'message' => 'Invalid credentials.'
            ], 401); //401 - unauthorized
        }


        $token = $user->createToken('shaq_didi_token')->plainTextToken;

        return Response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ], 200);
    }



    // fetch current user profile
    public function me(Request $request)
    {
        return Response()->json($request->user(), 200); //returns currently logged in user object
    }


    // handle logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return Response()->json([
            'message' => 'Successfully logged out.'
        ], 200);
    }
}
