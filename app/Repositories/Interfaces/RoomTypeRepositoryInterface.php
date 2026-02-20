<?php

namespace App\Repositories\Interfaces;

interface RoomTypeRepositoryInterface extends EloquentRepositoryInterface
{
    public function getAll(?string $search = null, int $perPage = 10);
}
