<?php

namespace App\Repositories\Interfaces;

interface FacilityRepositoryInterface extends EloquentRepositoryInterface
{
    public function getAll(?string $search = null, int $perPage = 10);
}
