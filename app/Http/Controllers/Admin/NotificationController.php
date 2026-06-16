<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::where('receiver_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('admin.notification.index', compact('notifications'));
    }

    public function read($id)
    {
        $notification = Notification::findOrFail($id);

        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }

        if ($notification->url) {
            return redirect($notification->url);
        }

        return back();
    }
}