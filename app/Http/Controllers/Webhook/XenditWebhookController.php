<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Verifikasi Keamanan (Pastikan yang akses ini benar-benar Xendit)
        $xenditToken = $request->header('x-callback-token');
        if ($xenditToken !== env('XENDIT_WEBHOOK_TOKEN')) {
            Log::warning('Akses Webhook Xendit Ditolak: Token tidak valid.');
            return response()->json(['message' => 'Forbidden: Invalid token'], 403);
        }

        // 2. Ambil data dari Xendit
        $externalId = $request->input('external_id');
        $status = $request->input('status'); // Bisa 'PAID' atau 'EXPIRED'
        $paymentMethod = $request->input('payment_method'); // Misal: 'BNI', 'MANDIRI'

        // 3. Proses Logika Berdasarkan Status
        if ($status === 'PAID') {
            DB::transaction(function () use ($externalId, $paymentMethod) {

                // Cari data payment berdasarkan Invoice ID
                $payment = Payment::where('external_id', $externalId)->first();

                // Jika ketemu dan statusnya masih pending
                if ($payment && $payment->status === 'pending') {

                    // A. Update tabel payments
                    $payment->update([
                        'status' => 'paid',
                        'payment_method' => $paymentMethod
                    ]);

                    // B. Update tabel bookings menjadi 'confirmed'
                    $booking = Booking::find($payment->booking_id);
                    if ($booking) {
                        $booking->update(['status' => 'confirmed']);

                        // C. Kunci kamar! Update status rooms menjadi 'occupied'
                        $room = Room::find($booking->room_id);
                        if ($room) {
                            $room->update(['status' => 'occupied']);
                        }
                    }
                }
            });
        } elseif ($status === 'EXPIRED') {
            // Jika invoice kadaluwarsa (1 Jam setelah dibuat) dan belum dibayar, kita update statusnya menjadi 'expired' dan batalkan bookingnya
            DB::transaction(function () use ($externalId) {
                $payment = Payment::where('external_id', $externalId)->first();
                if ($payment && $payment->status === 'pending') {
                    $payment->update(['status' => 'expired']);

                    $booking = Booking::find($payment->booking_id);
                    if ($booking) {
                        $booking->update(['status' => 'cancelled']);

                        $activeBookingsCount = Booking::where('room_id', $booking->room_id)
                            ->where('status', 'confirmed')
                            ->where('check_out_date', '>=', now())
                            ->count();

                        // Jika aktif booking nya tidak ada maka 
                        if ($activeBookingsCount === 0) {
                            // Lepas kunci kamar agar bisa disewa orang lain
                            $room = Room::find($booking->room_id);
                            if ($room) {
                                $room->update(['status' => 'available']);
                            }
                        }
                    }
                }
            });
        }

        // Xendit mewajibkan kita membalas dengan status 200 OK
        return response()->json(['message' => 'Webhook berhasil diproses'], 200);
    }
}
