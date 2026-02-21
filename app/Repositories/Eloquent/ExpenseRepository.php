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
}
