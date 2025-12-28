<?php

namespace App\Http\Controllers;

use App\Events\ActionPerformed;
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

            // 🔔 Audit log
            event(new ActionPerformed([
                'actor_id' => auth()->id() ?? $user->id, // admin creating user or self
                'actor_role' => auth()->user()?->role ?? $user->role,
                'action' => 'register',
                'description' => 'Registered a new user' . " ({$user->email})" . " as {$user->role}",
            ]));

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

        event(new ActionPerformed([
            'actor_id' => $user->id,
            'actor_role' => $user->role,
            'action' => 'send_reset_link',
            'description' => "$user->email requested password reset link",
        ]));
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

        event(new ActionPerformed([
            'actor_id' => $user->id,
            'actor_role' => $user->role,
            'action' => 'reset_password',
            'description' => "$user->firstname, $user->lastname ($user->email) reset password successfully",
        ]));

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

            // Debug: log dispatch to help detect double-dispatch issues
            \Log::debug('Dispatching ActionPerformed event (login)', [
                'actor_id' => auth()->id(),
                'actor_role' => auth()->user()?->role,
                'time' => now()->toDateTimeString(),
            ]);

            event(new ActionPerformed([
                'actor_id' => auth()->id(),
                'actor_role' => auth()->user()->role,
                'action' => 'login',
                'description' => auth()->user()->firstname . ' ' . auth()->user()->lastname . ', ' . auth()->user()->email . " logged in successfully",
            ]));

            \Log::debug('Dispatched ActionPerformed event (login)', [
                'actor_id' => auth()->id(),
                'actor_role' => auth()->user()?->role,
                'time' => now()->toDateTimeString(),
            ]);

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
            'message' => 'Invalid credentials',
        ], 401);
    }

    // Logout Method
    public function logout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        // 🔥 Log audit BEFORE destroying tokens
        event(new ActionPerformed([
            'actor_id' => $user->id,
            'actor_role' => $user->role,
            'action' => 'logout',
            'description' => "($user->role) $user->firstname $user->lastname, ($user->email) logged out",
        ]));

        // ❌ Remove ALL tokens for this user (logout everywhere)
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
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

            event(new ActionPerformed([
                'actor_id' => auth()->id(),
                'actor_role' => auth()->user()?->role,
                'action' => 'edit_user',
                'description' => "Edited user {$user->email}",
            ]));

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
        try {

            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }
            // Soft delete the user
            $user->delete();
            event(new ActionPerformed([
                'actor_id' => auth()->id(),
                'actor_role' => auth()->user()?->role,
                'action' => 'disable_user',
                'description' => "Disabled user {$user->email}",
            ]));
            return response()->json([
                'success' => true,
                'message' => 'User disabled successfully',
                'user' => $user,
            ], 200);
            // return $user;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disable user',
                'error' => $e->getMessage()
            ], 500);
        }
        // Try to find the user
    }

    // Restore Delete User
    public function restoreUser($id)
    {
        try {

            $user = User::withTrashed()->find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }
            $user->restore();

            event(new ActionPerformed([
                'actor_id' => auth()->id(),
                'actor_role' => auth()->user()?->role,
                'action' => 'restore_user',
                'description' => "Restored user {$user->email}",
            ]));

            return response()->json([
                'success' => true,
                'message' => 'User restored successfully',
                'user' => $user,
            ], 200);
            // return $user;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
