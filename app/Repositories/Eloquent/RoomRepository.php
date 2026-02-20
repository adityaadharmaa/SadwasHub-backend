<?php

namespace App\Repositories\Eloquent;

use App\Models\Room;
use App\Repositories\Interfaces\RoomRepositoryInterface;

class RoomRepository extends BaseRepository implements RoomRepositoryInterface
{
    public function __construct(Room $model)
    {
        parent::__construct($model);
    }

    public function getAll(?string $search = null, ?string $status = null, ?string $roomTypeId = null, int $perPage = 10)
    {
        $query = $this->model->with('type');

        if (!empty($search)) {
            $query->where('room_number', 'like', '%' . $search . '%');
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($roomTypeId)) {
            $query->where('room_type_id', $roomTypeId);
        }

        return $query->orderBy('room_number', 'asc')->paginate($perPage);
    }
}
