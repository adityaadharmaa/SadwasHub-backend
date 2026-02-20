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

    public function getByUser(string $userId, int $perPage = 10)
    {
        return $this->model->with(['room.type', 'payments'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllWithFilters(array $filters = [], int $perPage = 10)
    {
        $query = $this->model->with(['user.profile', 'room.type', 'payments']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['room_id'])) {
            $query->where('room_id', $filters['room_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
