<?php

namespace App\Services\Role;

use App\Models\Permission;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Validation\ValidationException;

class RoleService extends BaseService
{
    protected $roleRepo;
    public function __construct(RoleRepositoryInterface $roleRepo)
    {
        $this->roleRepo = $roleRepo;
    }

    public function getAllRoles()
    {
        return $this->roleRepo->getAllWithPermissions();
    }

    public function getAllPermissions()
    {
        return Permission::all();
    }

    public function createRole(array $data)
    {
        $this->atomic(function () use ($data) {
            $roleData = [
                'name' => $data['name'],
                'guard_name' => 'api'
            ];

            $role = $this->roleRepo->create($roleData);
            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->load('permissions');
        });
    }

    public function updateRole($id, array $data)
    {
        return $this->atomic(function () use ($id, $data) {
            $role = $this->roleRepo->find($id);

            if (!$role) {
                throw ValidationException::withMessages(['id' => 'Role tidak ditemukan.']);
            }

            // Cegah modifikasi nama role bawaan
            if (in_array($role->name, ['admin', 'tenant']) && isset($data['name']) && $data['name'] !== $role->name) {
                throw ValidationException::withMessages(['name' => 'Nama Role bawaan sistem tidak boleh diubah.']);
            }

            $updateData = [];
            if (isset($data['name'])) {
                $updateData['name'] = $data['name'];
                $this->roleRepo->update($id, $updateData);
            }

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->fresh()->load('permissions');
        });
    }

    public function deleteRole($id)
    {
        return $this->atomic(function () use ($id) {
            $role = $this->roleRepo->find($id);

            if (!$role) {
                throw ValidationException::withMessages(['id' => 'Role tidak ditemukan.']);
            }

            // Cegah penghapusan role krusial
            if (in_array($role->name, ['admin', 'tenant'])) {
                throw ValidationException::withMessages(['role' => 'Role bawaan sistem tidak boleh dihapus.']);
            }

            return $this->roleRepo->delete($id);
        });
    }
}
