<?php

namespace App\Repositories\Eloquent;

use App\Models\Promo;
use App\Repositories\Interfaces\PromoRepositoryInterface;

class PromoRepository extends BaseRepository implements PromoRepositoryInterface
{

    public function __construct(Promo $model)
    {
        parent::__construct($model);
    }

    public function getActivePromos()
    {
        return $this->model
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
    }
}
