<?php

namespace App\Services\Tenant;

use App\Repositories\Interfaces\TenantRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Validation\ValidationException;

class TenantService extends BaseService
{
    protected $tenantRepo;
    public function __construct(TenantRepositoryInterface $tenantRepo)
    {
        $this->tenantRepo = $tenantRepo;
    }

    public function getAllTenants(?string $search = null, ?string $status = null, int $perPage = 10)
    {
        return $this->tenantRepo->getAllTenants($search, $status, $perPage);
    }

    public function getTenantById($id)
    {
        $tenant = $this->tenantRepo->findTenantById($id);
        if (!$tenant) {
            throw ValidationException::withMessages(['id' => 'Data penghuni tidak ditemukan.']);
        }
        return $tenant;
    }
}
