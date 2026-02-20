<?php

namespace App\Repositories\Interfaces;

interface TicketRepositoryInterface extends EloquentRepositoryInterface
{
    public function getByUser(string $userId, int $perPage = 10);
    public function getAllWithFilters(array $filters = [], int $perPage = 10);
}
