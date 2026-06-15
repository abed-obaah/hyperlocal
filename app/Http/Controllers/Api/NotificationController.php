<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->appNotifications()->limit(100)->get();

        return response()->json([
            'unread' => $notifications->whereNull('read_at')->count(),
            'data' => $notifications->map(fn (UserNotification $n) => [
                'id' => (string) $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'body' => $n->body,
                'icon' => $n->icon ?? 'notifications',
                'data' => $n->data ?? [],
                'read' => $n->read_at !== null,
                'date' => optional($n->created_at)->toISOString(),
            ]),
        ]);
    }

    public function markRead(Request $request, UserNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->appNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
