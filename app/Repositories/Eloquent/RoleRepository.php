<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Interfaces\RoleRepositoryInterface;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(Role $model)
    {
        return parent::__construct($model);
    }

    public function getAllWithPermissions()
    {
        return $this->model->with('permissions');
    }
}
