<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate($request->query('per_page', 10));

        $formattedData = $notifications->getCollection()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->data['type'] ?? 'info',
                'title' => $notification->data['title'] ?? 'Notifikasi',
                'message' => $notification->data['message'] ?? '',
                'action_url' => $notification->data['action_url'] ?? '#',

                // INDIKATOR PENTING UNTUK FRONTEND:
                'is_read' => $notification->read_at !== null,

                'created_at_humans' => $notification->created_at->diffForHumans(),
                'created_at' => $notification->created_at->toIso8601String(),
            ];
        });

        $notifications->setCollection($formattedData);

        return $this->successResponse($notifications, 'Riwayat semua notifikasi berhasil diambil.');
    }

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
