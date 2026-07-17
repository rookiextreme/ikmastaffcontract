<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Paparkan senarai notifikasi pengguna.
     */
    public function index()
    {
        $notifications = Notification::where('receiver_id', Auth::id())
            ->latest()
            ->paginate(20);

        $unreadCount = Notification::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return view('admin.notification.index', compact(
            'notifications',
            'unreadCount'
        ));
    }

    /**
     * AJAX: Tandakan satu notifikasi sebagai dibaca.
     */
    public function markRead($id): JsonResponse
    {
        $notification = Notification::where('receiver_id', Auth::id())
            ->findOrFail($id);

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        $unreadCount = Notification::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi telah ditandakan sebagai dibaca.',
            'unread_count' => $unreadCount,
            'redirect_url' => $notification->url
                ?: route('notification.index'),
        ]);
    }

    /**
     * Fallback biasa tanpa AJAX.
     */
    public function read($id): RedirectResponse
    {
        $notification = Notification::where('receiver_id', Auth::id())
            ->findOrFail($id);

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        if (!empty($notification->url)) {
            return redirect($notification->url);
        }

        return redirect()->route('notification.index');
    }

    /**
     * Tandakan semua notifikasi sebagai dibaca.
     */
    public function readAll(): RedirectResponse
    {
        Notification::where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()
            ->route('notification.index')
            ->with('success', 'Semua pesanan telah ditandakan sebagai dibaca.');
    }
}