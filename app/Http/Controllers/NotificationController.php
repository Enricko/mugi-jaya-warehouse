<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        $query = Notification::where('user_id', $userId)->latest();

        if ($request->get('filter') === 'unread') {
            $query->where('is_read', false);
        }

        return view('notifications.index', [
            'notifications' => $query->paginate(20)->withQueryString(),
            'unreadCount' => Notification::where('user_id', $userId)->where('is_read', false)->count(),
            'filter' => $request->get('filter', 'all'),
        ]);
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        if (! $notification->is_read) {
            $notification->update(['is_read' => true, 'read_at' => now()]);
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'Semua notifikasi telah ditandai sebagai dibaca.');
    }
}
