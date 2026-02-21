<?php

namespace App\Services\Promo;

use App\Repositories\Interfaces\PromoRepositoryInterface;
use App\Services\BaseService;

class PromoService extends BaseService
{
    protected $promoRepo;
    public function __construct(PromoRepositoryInterface $promoRepo)
    {
        $this->promoRepo = $promoRepo;
    }

    public function getAllPromos(int $perPage = 10)
    {
        return $this->promoRepo->paginate($perPage);
    }

    public function getActivePromos()
    {
        return $this->promoRepo->getActivePromos();
    }

    public function createPromo(array $data)
    {
        return $this->promoRepo->create($data);
    }

    public function updatePromo($id, array $data)
    {
        $this->promoRepo->update($id, $data);
        return $this->promoRepo->find($id);
    }

    public function deletePromo($id)
    {
        return $this->promoRepo->delete($id);
    }
}
