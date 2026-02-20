<?php

namespace App\Repositories\Eloquent;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;

class PaymentRepository extends BaseRepository implements PaymentRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }
}
