<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenantResource;
use App\Services\Tenant\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    protected $tenantService;
    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = $request->query('perPage', 10);

        $tenants = $this->tenantService->getAllTenants($search, $status, $perPage);

        return TenantResource::collection($tenants)->additional(
            [
                'message' => 'Daftar tenant berhasil diambil',
                'status' => 'success'
            ]
        );
    }

    public function show($id)
    {
        $tenant = $this->tenantService->getTenantById($id);

        return $this->successResponse(
            new TenantResource($tenant),
            "Detail tenant berhasil diambil."
        );
    }
}
