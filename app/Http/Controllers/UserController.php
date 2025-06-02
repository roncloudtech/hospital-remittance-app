<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\UserTempPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'nullable|string|unique:users',
            'role' => 'required|in:admin,remitter'
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $tempPassword = Str::random(10);
        $user = new User;
        $user->firstname = $request->input('first_name');
        $user->lastname = $request->input('last_name');
        $user->email = $request->input('email');
        $user->password = $tempPassword;
        $user->phone_number = $request->input('phone_number');
        $user->role = $request->input('role');
        $user->save();

        Mail::to($user->email)->send( new \App\Mail\UserEmailVerification($user, $tempPassword));

        return response()->json([
            'user' => $user,
            'message' => "Register Successfully: Mail Sent",
        ], 201);

        
    }

    // User Reset Password
    public function passwordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'email' => 'required|email',
            'password' => 'required|string|same:confirm',
            'confirm' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $user = User::where('email', $request->email)->findOrFail($request->id)->first();

            if (!$user) {
                return response()->json([
                    'error' => 'Change of Password Failed',
                ], 400);
            }

            if ($user) {
                User::where('email', $user->email)->update([
                    'password' => $user->password,
                ]);
                $user->save();
                return response()->json([
                    'message' => 'Password changed successfully',
                ], 200);
            }
        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error,
            ], 500);
        }
    }

    // Send Reset Password Email
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email
        ]);
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
        if ($request->user()) {
            $request->user()->tokens()->delete();
            return response()->json([
                'message' => 'Logout successful'
            ]);
        }

        return response()->json([
            'message' => 'User not authenticated'
        ], 401);
    }

    public function getUsers()
    {
        // Fetch all users using Eloquent ORM
        $users = User::all();
        return $users;
    }
}
