<?php

namespace App\Repositories\Interfaces;

interface ExpenseRepositoryInterface extends EloquentRepositoryInterface
{
    public function getAllWithFilters(int $perPage = 10, ?string $search = null, ?string $category = null);
}
