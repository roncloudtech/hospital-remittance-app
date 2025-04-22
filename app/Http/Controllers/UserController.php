<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {
        // Validate user entry
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'nullable|string|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,remitter'
        ]);

        if($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400) ;
        }

        // Creating a new user with the entries
        $user = new User;
        $user->firstname = $request->input('first_name');
        $user->lastname = $request->input('last_name');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->phone_number = $request->input('phone_number');
        $user->role = $request->input('role');
        $user->save();

        return response()->json([
            'message' => $user->role . ' account created successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('military-health-token')->plainTextToken;

            return response()->json([
                'message' => 'Authentication successful',
                // 'user' => $user,
                'user' => [
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'role' => $user->role,
                    'email' => $user->email
                ],
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'Invalid military credentials'
        ], 401);
    }

    public function logout(Request $request)
    {
        // Revoke the current access token
        // $request->user()->currentAccessToken()->delete();
        // Revoke all tokens for the user
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }

    public function getUsers() {
        // Fetch all users using Eloquent ORM
        $users = User::all();
        return $users;
    }
}
