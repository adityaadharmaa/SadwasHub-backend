<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\Booking\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->query('status'),
            'room_id' => $request->query('room_id'),
        ];
        $perPage = $request->query('per_page', 10);

        $bookings = $this->bookingService->getAllBookings($filters, $perPage);

        return BookingResource::collection($bookings)->additional([
            'message' => 'Daftar seluruh booking berhasil diambil.',
            'status' => 'success'
        ]);
    }

    public function myBookings(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $bookings = $this->bookingService->getTenantBookings($request->user()->id, $perPage);

        return BookingResource::collection($bookings)->additional([
            'message' => 'Daftar booking Anda berhasil diambil.',
            'status' => 'success'
        ]);
    }

    public function store(StoreBookingRequest $request)
    {
        $user = $request->user();
        $booking = $this->bookingService->createBooking($user, $request->validated());

        return $this->successResponse($booking, 'Booking created successfully', 201);
    }
}
