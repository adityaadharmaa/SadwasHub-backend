<?php

namespace App\Repositories\Interfaces;

interface RoomRepositoryInterface extends EloquentRepositoryInterface
{
    public function getAll(?string $search = null, ?string $status = null, ?string $roomTypeId = null, int $perPage = 10);
}
