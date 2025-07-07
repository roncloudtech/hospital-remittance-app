<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    // User Registration
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

        try {
            $tempPassword = Str::random(10);
            $user = new User;
            $user->firstname = $request->input('first_name');
            $user->lastname = $request->input('last_name');
            $user->email = $request->input('email');
            $user->password = $tempPassword;
            $user->phone_number = $request->input('phone_number');
            $user->role = $request->input('role');
            $user->save();

            Mail::to($user->email)->send(new \App\Mail\UserEmailVerification($user, $tempPassword));

            return response()->json([
                'user' => $user,
                'message' => "Register Successfully: Mail Sent",
            ], 201);
        } catch (\Exception $error) {
            return response()->json([
                'errors' => $error,
                'message' => "Registration Failed",
            ], 500);
        }
    }

    // Password Reset
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email not found'], 404);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        $resetLink = env('FRONTEND_URL') . "/reset-password?token={$token}&email={$user->email}";


        // Send email
        Mail::raw("Reset your password using this link: $resetLink", function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Password Reset');
        });

        return response()->json(['message' => 'Reset link sent to your email']);
    }

    // Step 2: Reset Password
    public function resetPassword(Request $request)
    {
        // $request->validate([
        //     'token' => 'required|string',
        //     'email' => 'required|email',
        //     'password' => 'required|string|min:8|confirmed',
        // ]);
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|same:password_confirmation',
            'password_confirmation' => 'required|string|min:8|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(["errors" => $validator->errors()], 400);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record || Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return response()->json(['message' => 'Invalid or expired token'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = $request->password;
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been reset successfully']);
    }

    // Login Methods
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
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid military credentials',

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

    
    // Fetch a single user
    public function getUser($id)
    {
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }
    
    // Update User Details
    public function editUser($id, Request $request)
    {
        // Try to find the user
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Validate input and exclude current user from unique checks
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$id}",
            'phone_number' => "nullable|string|unique:users,phone_number,{$id}",
            'role' => 'required|in:admin,remitter',
        ]);

        try {
            // Update user fields
            $user->firstname = $validated['first_name'];
            $user->lastname = $validated['last_name'];
            $user->email = $validated['email'];
            $user->phone_number = $validated['phone_number'];
            $user->role = $validated['role'];
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // getall users except admin
    public function getUsers()
    {
        // Fetch all users using Eloquent ORM
        $users = User::withTrashed()->where('role', 'remitter')->get();
        return $users;
    }

    // Soft Delete User
    public function deleteUser($id)
    {
        // Try to find the user
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
        // Soft delete the user
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User disabled successfully',
            'user' => $user,
        ],200);
        // return $user;
    }

    // Restore Delete User
    public function restoreUser($id) 
    {
        $user = User::withTrashed()->find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }
        $user->restore();
        return response()->json([
            'success' => true,
            'message' => 'User restored successfully',
            'user' => $user,
        ],200);  
        // return $user;
    }
}
