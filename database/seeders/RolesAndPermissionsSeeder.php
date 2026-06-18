<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'churches.view',
            'churches.create',
            'churches.update',
            'churches.delete',
            'churches.suspend',
            'churches.activate',
            'churches.impersonate',
            'subscriptions.view',
            'subscriptions.manage',
            'payments.view',
            'payments.manage',
            'users.view',
            'users.manage',
            'settings.manage',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $owner->syncPermissions(Permission::all());

        $staff = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $staff->syncPermissions([
            'dashboard.view',
            'churches.view',
            'subscriptions.view',
            'payments.view',
        ]);

        Role::firstOrCreate(['name' => 'church_admin', 'guard_name' => 'web']);
    }
}
