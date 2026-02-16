<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permission List
        $permissions = [
            'manage-rooms',
            'view-rooms',
            'create-booking',
            'manage-all-bookings',
            'view-own-booking',
            'verify-ktp',
            'manage-users',
            'edit-profile',
            'manage-payments',
            'view-own-payments',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            // Karena pakai App\Models\Permission (yang punya HasUuids), ID akan otomatis terisi
            Permission::create(['name' => $permission, 'guard_name' => 'api']);
        }

        // Create Roles
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo(Permission::all());

        $tenantRole = Role::create(['name' => 'tenant', 'guard_name' => 'api']);
        $tenantRole->givePermissionTo([
            'view-rooms',
            'create-booking',
            'view-own-booking',
            'edit-profile',
            'view-own-payments'
        ]);
    }
}
