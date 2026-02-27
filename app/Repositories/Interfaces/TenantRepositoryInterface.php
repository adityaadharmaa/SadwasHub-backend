<?php

namespace App\Repositories\Interfaces;

interface TenantRepositoryInterface extends EloquentRepositoryInterface
{
    public function getAllTenants(?string $search = null, ?string $status = null, int $perPage = 10);
    public function findTenantById($id);
}
