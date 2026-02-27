<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class TenantRepository extends BaseRepository implements TenantRepositoryInterface
{
    public function __construct(User $model)
    {
        return parent::__construct($model);
    }

    public function getAllTenants(?string $search = null, ?string $status = null, int $perPage = 10)
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'tenant');
        })
            ->with(['profile']) // HAPUS 'verification' DARI SINI
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                        ->orWhereHas('profile', function ($qProfile) use ($search) {
                            $qProfile->where('full_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($query) use ($status) {
                $query->whereHas('profile', function ($q) use ($status) {
                    if ($status === 'verified') {
                        $q->where('is_verified', true);
                    } elseif ($status === 'pending') {
                        $q->where('is_verified', false)->whereNotNull('ktp_path'); // Cek ktp_path, bukan ktp_url
                    } elseif ($status === 'unverified') {
                        $q->where('is_verified', false)->whereNull('ktp_path');
                    }
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findTenantById($id)
    {
        return User::with(['profile', 'verification'])->find($id);
    }
}
