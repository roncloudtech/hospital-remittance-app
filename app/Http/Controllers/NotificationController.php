<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications (latest first)
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'notifications' => $user->notifications()->latest()->get(),
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications()->count(),
        ]);
        // return response()->json([
        //     'count' => $request->user()->unreadNotifications()->count()
        // ]);
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $notification = auth()->user()
                ->notifications()
                ->where('id', $id)
                ->firstOrFail();
    
            $notification->markAsRead();
    
            return response()->json([
                'message' => 'Notification marked as read',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Notification not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        auth()->user()
            ->unreadNotifications
            ->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read',
        ]);
    }
}
