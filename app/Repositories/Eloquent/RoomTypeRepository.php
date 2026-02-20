<?php

namespace App\Repositories\Eloquent;

use App\Models\RoomType;
use App\Repositories\Interfaces\RoomTypeRepositoryInterface;

class RoomTypeRepository extends BaseRepository implements RoomTypeRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(RoomType $model)
    {
        parent::__construct($model);
    }

    public function getAll(?string $search = null, int $perPage = 10)
    {
        $query = $this->model->with('facilities');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
