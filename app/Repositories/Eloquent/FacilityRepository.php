<?php

namespace App\Repositories\Eloquent;

use App\Models\Facilitie;
use App\Repositories\Interfaces\FacilityRepositoryInterface;

class FacilityRepository extends BaseRepository implements FacilityRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(Facilitie $model)
    {
        parent::__construct($model);
    }

    public function getAll(?string $search = null, int $perPage = 10)
    {
        $query = $this->model->newQuery();

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->orderBy('name', 'asc')->paginate($perPage);
    }
}
