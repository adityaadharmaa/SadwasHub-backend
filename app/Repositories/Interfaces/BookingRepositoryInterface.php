<?php

namespace App\Repositories\Interfaces;

interface BookingRepositoryInterface extends EloquentRepositoryInterface
{
    public function getByUser(string $userId, int $perPage = 10);
    public function getAllWithFilters(array $filtrers = [], int $perPage = 10);
}
