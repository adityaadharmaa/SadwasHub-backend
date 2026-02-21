<?php

namespace App\Repositories\Interfaces;

interface PromoRepositoryInterface extends EloquentRepositoryInterface
{
    public function getActivePromos();
}
