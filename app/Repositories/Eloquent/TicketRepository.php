<?php

namespace App\Repositories\Eloquent;

use App\Models\Ticket;
use App\Repositories\Interfaces\TicketRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class TicketRepository extends BaseRepository implements TicketRepositoryInterface
{
    public function __construct(Ticket $model)
    {
        return parent::__construct($model);
    }

    public function getByUser(string $userId, int $perPage = 10)
    {
        return $this->model->with(['room.type'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllWithFilters(array $filters = [], int $perPage = 10)
    {
        $query = $this->model->with(['user.profile', 'room.type']);

        if (!empty($filters['statys'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
