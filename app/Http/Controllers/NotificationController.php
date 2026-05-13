<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;

class NotificationController extends Controller
{
    // Notifications List
    public function index()
    {
        $notifications = ErpNotification::forUser(auth()->id())
            ->latest()
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    // Mark Single Notification as Read
    public function markRead($id)
    {
        $notification = ErpNotification::forUser(auth()->id())
            ->findOrFail($id);

        $notification->markAsRead();

        if ($notification->url) {
            return redirect($notification->url);
        }

        return back()->with(
            'success',
            'Notification marked as read.'
        );
    }

    // Mark All Notifications as Read
    public function markAllRead()
    {
        ErpNotification::forUser(auth()->id())
            ->unread()
            ->update([
                'read_at' => now()
            ]);

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }

    // Unread Notification Count
    public function unreadCount()
    {
        return response()->json([
            'count' => ErpNotification::forUser(auth()->id())
                ->unread()
                ->count()
        ]);
    }
}