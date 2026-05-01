<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = ErpNotification::forUser(auth()->id())
            ->latest()
            ->paginate(30);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(ErpNotification $notification)
    {
        $notification->markAsRead();
        if ($notification->url) {
            return redirect($notification->url);
        }
        return back();
    }

    public function markAllRead()
    {
        ErpNotification::forUser(auth()->id())->unread()->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => ErpNotification::forUser(auth()->id())->unread()->count()
        ]);
    }
}
