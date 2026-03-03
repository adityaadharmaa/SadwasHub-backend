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

    // Biaya Admin Flat
    protected $adminFee = 5000;

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
        $room = Room::with('type')->find($data['room_id']);
        if ($room->status !== 'available') {
            throw ValidationException::withMessages(['room_id' => 'Mohon maaf, kamar yang Anda pilih sedang tidak tersedia.']);
        }

        $duration = $data['duration'];
        $rentType = $data['rent_type'];
        
        $checkInDate = Carbon::parse($data['check_in_date']);
        $checkOutDate = $checkInDate->copy();
        $subTotal = 0;

        if ($rentType === 'daily') {
            $checkOutDate->addDays($duration);
            $subTotal = $room->type->price_per_day * $duration;
        } elseif ($rentType === 'weekly') {
            $checkOutDate->addWeeks($duration);
            $subTotal = $room->type->price_per_week * $duration;
        } elseif ($rentType === 'monthly') {
            $checkOutDate->addMonths($duration);
            $subTotal = $room->type->price_per_month * $duration;
        }

        $discountAmount = 0;
        $promoId = null;
        $promo = null;

        if (!empty($data['promo_code'])) {
            $promo = Promo::where('code', $data['promo_code'])
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->where(function ($query) {
                    $query->whereNull('limit')
                        ->orWhere('limit', '>', 0);
                })
                ->first();

            if (!$promo) {
                throw ValidationException::withMessages(['promo_code' => 'Kode promo tidak valid, sudah kedaluwarsa, atau kuota habis.']);
            }

            if ($promo->type === 'percentage') {
                $discountAmount = $subTotal * ($promo->reward_amount / 100);
            } else {
                $discountAmount = $promo->reward_amount;
            }
            $promoId = $promo->id;
        }

        $priceAfterDiscount = $subTotal - $discountAmount;
        if ($priceAfterDiscount < 0) {
            $priceAfterDiscount = 0;
        }

        $totalAmount = $priceAfterDiscount + $this->adminFee;

        return $this->atomic(function () use ($user, $room, $promo, $promoId, $checkInDate, $checkOutDate, $totalAmount, $discountAmount, $duration, $rentType, $priceAfterDiscount, $data) {
            
            $booking = $this->bookingRepo->create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'promo_id' => $promoId,
                'rent_type' => $rentType, 
                'check_in_date' => $checkInDate->toDateString(),
                'check_out_date' => $checkOutDate->toDateString(),
                'total_amount' => $totalAmount, 
                'discount_amount' => $discountAmount,
                'notes' => $data['notes'] ?? null, 
                'status' => 'pending'
            ]);

            $shortUuid = strtoupper(substr($booking->id, 0, 6));
            $externalId = 'INV-SC-' . date('Ymd') . '-' . $shortUuid;
            $timeLabel = $rentType === 'daily' ? 'hari' : ($rentType === 'weekly' ? 'minggu' : 'bulan');
            $description = "Sewa {$rentType} Kamar {$room->room_number} untuk {$duration} {$timeLabel}";

            // --- 1. SIAPKAN RINCIAN ITEM (KAMAR) ---
            $items = [
                [
                    'name' => "Kamar {$room->room_number} ({$duration} {$timeLabel})",
                    'quantity' => 1,
                    'price' => $priceAfterDiscount,
                    'category' => 'Akomodasi'
                ]
            ];

            // --- 2. SIAPKAN RINCIAN BIAYA ADMIN ---
            $fees = [
                [
                    'type' => 'Biaya Layanan Aplikasi',
                    'value' => $this->adminFee
                ]
            ];

            // --- 3. KIRIM KE XENDIT BESERTA ITEM & FEE ---
            $xenditResponse = $this->xenditService->createInvoice(
                $externalId,
                $totalAmount,
                $user->email,
                $description,
                $items, // Kirim payload items
                $fees   // Kirim payload fees
            );

            $this->paymentRepo->create([
                'booking_id' => $booking->id,
                'external_id' => $externalId,
                'amount' => $totalAmount,
                'status' => 'pending',
                'checkout_url' => $xenditResponse['invoice_url']
            ]);

            $room->update(['status' => 'occupied']);

            if ($promo && $promo->limit !== null) {
                $promo->decrement('limit');
            }

            return $booking->load(['room.type', 'payments']);
        });
    }

    public function extendBooking($user, $bookingId, array $data)
    {
        $oldBooking = $this->bookingRepo->find($bookingId);

        if (!$oldBooking || $oldBooking->user_id !== $user->id || $oldBooking->status !== 'confirmed') {
            throw ValidationException::withMessages(['booking' => 'Data sewa tidak valid atau tidak bisa diperpanjang.']);
        }

        $room = Room::with('type')->find($oldBooking->room_id);
        
        $rentType = $data['rent_type'];
        $duration = $data['duration'];

        $checkInDate = Carbon::parse($oldBooking->check_out_date);
        $checkOutDate = $checkInDate->copy();
        
        $subTotal = 0;
        $timeLabel = '';

        if ($rentType === 'daily') {
            $checkOutDate->addDays($duration);
            $subTotal = $room->type->price_per_day * $duration;
            $timeLabel = 'hari';
        } elseif ($rentType === 'weekly') {
            $checkOutDate->addWeeks($duration);
            $subTotal = $room->type->price_per_week * $duration;
            $timeLabel = 'minggu';
        } elseif ($rentType === 'monthly') {
            $checkOutDate->addMonths($duration);
            $subTotal = $room->type->price_per_month * $duration;
            $timeLabel = 'bulan';
        }

        $totalAmount = $subTotal + $this->adminFee;

        return $this->atomic(function () use ($user, $room, $checkInDate, $checkOutDate, $totalAmount, $subTotal, $duration, $rentType, $timeLabel) {
            
            $newBooking = $this->bookingRepo->create([
                'user_id' => $user->id,
                'room_id' => $room->id,
                'promo_id' => null, 
                'rent_type' => $rentType, 
                'check_in_date' => $checkInDate->toDateString(),
                'check_out_date' => $checkOutDate->toDateString(),
                'total_amount' => $totalAmount, 
                'discount_amount' => 0,
                'status' => 'pending',
            ]);

            $shortUuid = strtoupper(substr($newBooking->id, 0, 6)); 
            $externalId = 'INV-EXT-' . date('Ymd') . '-' . $shortUuid;
            $description = "Perpanjangan {$rentType} Kamar {$room->room_number} untuk {$duration} {$timeLabel}";

            // --- 1. SIAPKAN RINCIAN ITEM PERPANJANGAN ---
            $items = [
                [
                    'name' => "Perpanjangan Kamar {$room->room_number} ({$duration} {$timeLabel})",
                    'quantity' => 1,
                    'price' => $subTotal,
                    'category' => 'Akomodasi'
                ]
            ];

            // --- 2. SIAPKAN RINCIAN BIAYA ADMIN ---
            $fees = [
                [
                    'type' => 'Biaya Layanan Aplikasi',
                    'value' => $this->adminFee
                ]
            ];

            // --- 3. KIRIM KE XENDIT ---
            $xenditResponse = $this->xenditService->createInvoice(
                $externalId,
                $totalAmount,
                $user->email,
                $description,
                $items, // Kirim payload items
                $fees   // Kirim payload fees
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