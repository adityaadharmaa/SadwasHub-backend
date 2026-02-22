<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function unread(Request $request)
    {
        $notifications = $request->user()->unreadNotifications()->get();

        $formattedData = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? 'info',
                'title' => $notification->data['title'] ?? 'Notifikasi Baru',
                'message' => $notification->data['message'] ?? '',
                'action_url' => $notification->data['action_url'] ?? '#',
                'created_at' => $notification->created_at->diffForHumans(), // Contoh: "2 minutes ago"
            ];
        });

        return $this->successResponse($formattedData, 'Notifikasi belum dibaca berhasil diambil.');
    }

    public function markAsRead(Request $request, $id)
    {
        $notifications = $request->user()->notifications()->where('id', $id)->first();

        if ($notifications) {
            $notifications->markAsRead();
        }

        return $this->successResponse(null, 'Notifikasi ditandai sudah dibaca.');
    }
}
