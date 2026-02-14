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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Management Kamar
            'manage-rooms',
            'view-rooms',

            // Management Booking
            'create-booking',
            'manage-all-bookings',
            'view-own-booking',

            // Management User & Profil
            'verify-ktp',
            'manage-users',
            'edit-profile',

            // Management Keuangan
            'manage-payments',
            'view-own-payments',

            // Settings
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'api']);
        }

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
