<?php

namespace App\Helpers;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    public static function send(
        $receiverId,
        $title,
        $message,
        $url = null,
        $module = null,
        $type = 'info'
    ) {
        try {

            Notification::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'module' => $module,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'url' => $url,
            ]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}