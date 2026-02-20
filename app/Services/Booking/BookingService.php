<?php

namespace App\Services\Booking;

use App\Models\Promo;
use App\Models\Room;
use App\Repositories\Interfaces\BookingRepositoryInterface;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Services\BaseService;
use App\Services\Payment\XenditService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingService extends BaseService
{
    protected $bookingRepo;
    protected $paymentRepo;
    protected $xenditService;

    public function __construct(
        BookingRepositoryInterface $bookingRepo,
        PaymentRepositoryInterface $paymentRepo,
        XenditService $xenditService
    ) {
        $this->bookingRepo = $bookingRepo;
        $this->paymentRepo = $paymentRepo;
        $this->xenditService = $xenditService;
    }

    public function getTenantBookings($userId, int $perPage = 10)
    {
        return $this->bookingRepo->getByUser($userId, $perPage);
    }

    public function getAllBookings(array $filters = [], int $perPage = 10)
    {
        return $this->bookingRepo->getAllWithFilters($filters, $perPage);
    }

    public function createBooking($user, array $data)
    {
        // 1. Melakukan validasi ketersediaan kamar
        $room = Room::with('type')->find($data['room_id']);
        if ($room->status !== 'available') {
            throw ValidationException::withMessages(['room_id' => 'Mohon maaf, kamar yang Anda pilih sedang tidak tersedia.']);
        }

        // 2. Menghitung Tanggal dan Harga Subtotal
        $duration = $data['duration_months'];
        $checkInDate = Carbon::parse($data['check_in_date']);
        $checkOutDate = $checkInDate->copy()->addMonths($duration);

        $pricePerMonth = $room->type->price_per_month;
        $subTotal = $pricePerMonth * $duration;

        // 3. Menerapkan Promo (jika ada)
        $discountAmount = 0;
        $promoId = null;

        if (!empty($data['promo_code'])) {
            $promo = Promo::where('code', $data['promo_code'])
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            if (!$promo) {
                throw ValidationException::withMessages(['promo_code' => 'Kode promo tidak valid atau sudah tidak berlaku.']);
            }

            if ($promo->type === 'percentage') {
                $discountAmount = $subTotal * ($promo->reward_amount / 100);
            } else {
                $discountAmount = $promo->reward_amount;
            }
            $promoId = $promo->id;
        }

        $totalAmount = $subTotal - $discountAmount;

        if ($totalAmount < 0) $totalAmount = 0;

        return $this->atomic(function () use ($user, $room, $promoId, $checkInDate, $checkOutDate, $totalAmount, $discountAmount, $duration) {
            // A. Simpan data booking
            $booking = $this->bookingRepo->create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'promo_id' => $promoId,
                'check_in_date' => $checkInDate->toDateString(),
                'check_out_date' => $checkOutDate->toDateString(),
                'total_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'status' => 'pending'
            ]);

            // B. Minta Link Pembayaran dari Xendit
            $externalId = 'SC-' . $booking->id . '-' . time();
            $description = "Sewa Kamar {$room->room_number} untuk {$duration} bulan";

            $xenditResponse = $this->xenditService->createInvoice(
                $externalId,
                $totalAmount,
                $user->email,
                $description
            );

            // C. Simpan Data Payment dari response Xendit
            $this->paymentRepo->create([
                'booking_id' => $booking->id,
                'external_id' => $externalId,
                'amount' => $totalAmount,
                'status' => 'pending',
                'checkout_url' => $xenditResponse['invoice_url']
            ]);

            // D. Kunci kamar dengan status 'occupied' agar tidak bisa dipesan orang lain
            $room->update(['status' => 'occupied']);

            return $booking->load(['room.type', 'payments']);
        });
    }

    public function extendBooking($user, $bookingId, array $data)
    {
        $oldBooking = $this->bookingRepo->find($bookingId);

        if (!$oldBooking || $oldBooking->user_id !== $user->id || $oldBooking->status !== 'confirmed') {
            throw ValidationException::withMessages(['booking' => 'Data sewa tidak valid atau tidak bisa di perpanjang.']);
        }

        $room = Room::with('type')->find($oldBooking->room_id);
        $duration = $data['duration_months'];

        $checkInDate = Carbon::parse($oldBooking->check_out_date);
        $checkOutDate = $checkInDate->copy()->addMonths($duration);

        $pricePerMonth = $room->type->price_per_month;
        $totalAmount = $pricePerMonth * $duration;

        return $this->atomic(function () use ($user, $room, $checkInDate, $checkOutDate, $totalAmount, $duration) {
            $newBooking = $this->bookingRepo->create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'promo_id' => null,
                'check_in_date' => $checkInDate->toDateString(),
                'check_out_date' => $checkOutDate->toDateString(),
                'total_amount' => $totalAmount,
                'discount_amount' => 0,
                'status' => 'pending',
            ]);

            $externalId = 'SC-EXT-' . $newBooking->id . '-' . time();
            $description = "Perpanjang Sewa Kamar {$room->room_number} untuk {$duration} bulan";

            $xenditResponse = $this->xenditService->createInvoice(
                $externalId,
                $totalAmount,
                $user->email,
                $description
            );

            $this->paymentRepo->create([
                'booking_id' => $newBooking->id,
                'external_id' => $externalId,
                'amount' => $totalAmount,
                'status' => 'pending',
                'checkout_url' => $xenditResponse['invoice_url']
            ]);

            return $newBooking->load(['room.type', 'payments']);
        });
    }
}
