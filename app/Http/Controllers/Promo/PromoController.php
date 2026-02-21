<?php

namespace App\Http\Controllers\Promo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Promo\StorePromoRequest;
use App\Http\Requests\Promo\UpdatePromoRequest;
use App\Http\Resources\PromoResource;
use App\Services\Promo\PromoService;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    protected $promoService;
    public function __construct(PromoService $promoService)
    {
        $this->promoService = $promoService;
    }

    // Admin ENDPOINTS
    public function index(Request $request)
    {
        $promos = $this->promoService->getAllPromos($request->query('per_page', 10));
        return PromoResource::collection($promos);
    }

    public function store(StorePromoRequest $request)
    {
        $promo = $this->promoService->createPromo($request->validated());
        return $this->successResponse(new PromoResource($promo), 'Kode promo berhasil ditambahkan.', 201);
    }

    public function update(UpdatePromoRequest $request, $id)
    {
        $promo = $this->promoService->updatePromo($id, $request->validated());
        return $this->successResponse(new PromoResource($promo), 'Kode promo berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->promoService->deletePromo($id);
        return $this->successResponse(null, 'Promo berhasil dihapus.');
    }
    // End Admin ENDPOINTS

    // Tenant ENDPOINTS
    public function activePromos()
    {
        $promos = $this->promoService->getActivePromos();
        return $this->successResponse(PromoResource::collection($promos), 'Daftar promo aktif.');
    }
}
