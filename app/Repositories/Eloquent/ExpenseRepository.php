<?php

namespace App\Repositories\Eloquent;

use App\Models\Expense;
use App\Repositories\Interfaces\ExpenseRepositoryInterface;

class ExpenseRepository extends BaseRepository implements ExpenseRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(Expense $model)
    {
        parent::__construct($model);
    }

    // Implementasi fungsi filter
    public function getAllWithFilters(int $perPage = 10, ?string $search = null, ?string $category = null)
    {
        return $this->model->with(['room', 'attachments'])
            ->when($search, function ($query) use ($search) {
                // Bungkus dalam function agar 'OR' tidak bocor ke logika query lain
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->orderBy('expense_date', 'desc')
            ->paginate($perPage);
    }
}
