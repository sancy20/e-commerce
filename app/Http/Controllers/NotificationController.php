<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get unread notifications for the authenticated user.
     */
    public function getUnreadNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $unreadNotifications = $user->unreadNotifications()
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get()
                                    ->map(function($notification) {
                                        $data = $notification->data; // Laravel automatically casts to array/object
                                        
                                        // Ensure $data is an array to modify it safely
                                        if (is_object($data)) {
                                            $data = (array)$data; // Convert object to array
                                        }

                                        // Add required frontend fields
                                        $data['is_read'] = $notification->read_at !== null;
                                        $data['notification_id'] = $notification->id;
                                        $data['created_at_for_humans'] = $notification->created_at->diffForHumans();
                                        
                                        return $data;
                                    });

        Log::info('Fetched ' . $unreadNotifications->count() . ' unread notifications for user ID: ' . $user->id);
        return response()->json([
            'count' => $unreadNotifications->count(),
            'notifications' => $unreadNotifications,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        if ($notification->read_at === null) {
            $notification->markAsRead();
            Log::info('Notification ID: ' . $notificationId . ' marked as read by user ID: ' . $user->id);
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'already read']);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->unreadNotifications->markAsRead();
        Log::info('All unread notifications marked as read by user ID: ' . $user->id);
        return response()->json(['status' => 'success']);
    }

    /**
     * Delete a specific notification.
     */
    public function destroy(Request $request, $notificationId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();
        Log::info('Notification ID: ' . $notificationId . ' deleted by user ID: ' . $user->id);
        return response()->json(['status' => 'success']);
    }
}