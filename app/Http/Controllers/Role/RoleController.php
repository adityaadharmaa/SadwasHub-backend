<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\Role\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    // --- GET SEMUA ROLE ---
    public function index()
    {
        // Bypassing repository untuk memastikan kembaliannya adalah Eloquent Collection murni
        $roles = Role::with('permissions')->get();
        return $this->successResponse(RoleResource::collection($roles), 'Data Role berhasil diambil.');
    }

    // --- GET SEMUA PERMISSION ---
    public function permissions()
    {
        // Bypassing repository
        $permissions = Permission::all();
        return $this->successResponse(PermissionResource::collection($permissions), 'Data Permission berhasil diambil.');
    }

    // --- CREATE ROLE BARU ---
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name' // Validasi pastikan nama permission valid
        ]);

        $role = $this->roleService->createRole($validated);

        return $this->successResponse(new RoleResource($role), 'Role baru berhasil dibuat.', 201);
    }

    // --- UPDATE ROLE & SYNC PERMISSIONS ---
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $role = $this->roleService->updateRole($id, $validated);

        return $this->successResponse(new RoleResource($role), 'Role berhasil diperbarui.');
    }

    // --- DELETE ROLE ---
    public function destroy($id)
    {
        $this->roleService->deleteRole($id);
        return $this->successResponse(null, 'Role berhasil dihapus.');
    }
}
