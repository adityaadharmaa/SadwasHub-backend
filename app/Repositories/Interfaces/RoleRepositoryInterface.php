<?php

namespace App\Repositories\Interfaces;

interface RoleRepositoryInterface extends EloquentRepositoryInterface
{
    public function getAllWithPermissions();
}
