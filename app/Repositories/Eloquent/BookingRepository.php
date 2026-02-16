<?php

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Interfaces\BookingRepositoryInterface;

class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(Booking $model)
    {
        parent::__construct($model);
    }

    public function getActiveBookings($userId)
    {
        return $this->model->where('user_id', $userId)
            ->whereIn('status', ['confirmed', 'paid'])
            ->get();
    }
}
