<?php

namespace Database\Seeders;

use App\Enums\ChurchStaffRole;
use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ChurchRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'members.view',
            'members.create',
            'members.update',
            'members.delete',
            'leadership.view',
            'leadership.manage',
            'departments.view',
            'departments.manage',
            'announcements.view',
            'announcements.manage',
            'services.view',
            'services.manage',
            'special_events.view',
            'special_events.manage',
            'attendance.view',
            'attendance.manage',
            'bereavements.view',
            'bereavements.manage',
            'finance.view',
            'finance.manage',
            'finance.approve',
            'reports.view',
            'analytics.view',
            'member_requests.view',
            'member_requests.manage',
            'branches.view',
            'branches.manage',
            'system.logs',
            'system.sessions',
            'system.users',
            'system.roles',
            'system.monitor',
            'system.settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $allChurchPermissions = Permission::whereIn('name', $permissions)->get();

        $nonSystemPermissions = $allChurchPermissions->filter(
            fn (Permission $permission) => ! str_starts_with($permission->name, 'system.')
        );

        $administrator = Role::firstOrCreate(['name' => ChurchStaffRole::Administrator->value, 'guard_name' => 'web']);
        $administrator->syncPermissions($allChurchPermissions);

        Role::firstOrCreate(['name' => 'church_admin', 'guard_name' => 'web'])
            ->syncPermissions($allChurchPermissions);

        $pastor = Role::firstOrCreate(['name' => ChurchStaffRole::Pastor->value, 'guard_name' => 'web']);
        $pastor->syncPermissions($nonSystemPermissions);

        $secretaryPermissions = $nonSystemPermissions->filter(
            fn (Permission $permission) => ! str_starts_with($permission->name, 'finance.')
        );

        $secretary = Role::firstOrCreate(['name' => ChurchStaffRole::Secretary->value, 'guard_name' => 'web']);
        $secretary->syncPermissions($secretaryPermissions);

        $treasurerPermissions = $nonSystemPermissions->filter(function (Permission $permission) {
            return str_starts_with($permission->name, 'finance.')
                || in_array($permission->name, [
                    'reports.view',
                    'analytics.view',
                    'members.view',
                    'bereavements.view',
                    'bereavements.manage',
                ], true);
        });

        $treasurer = Role::firstOrCreate(['name' => ChurchStaffRole::Treasurer->value, 'guard_name' => 'web']);
        $treasurer->syncPermissions($treasurerPermissions);

        $accountantPermissions = $nonSystemPermissions->filter(function (Permission $permission) {
            return in_array($permission->name, [
                'finance.view',
                'finance.manage',
                'reports.view',
                'analytics.view',
                'members.view',
                'bereavements.view',
                'bereavements.manage',
            ], true);
        });

        $accountant = Role::firstOrCreate(['name' => ChurchStaffRole::Accountant->value, 'guard_name' => 'web']);
        $accountant->syncPermissions($accountantPermissions);

        Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

        User::query()
            ->where('user_type', UserType::ChurchAdmin->value)
            ->each(function (User $user): void {
                if (! $user->hasRole(ChurchStaffRole::Administrator->value)) {
                    $user->assignRole(ChurchStaffRole::Administrator->value);
                }
            });

        User::query()
            ->where('user_type', UserType::Pastor->value)
            ->each(function (User $user): void {
                if (! $user->hasRole(ChurchStaffRole::Pastor->value)) {
                    $user->assignRole(ChurchStaffRole::Pastor->value);
                }
            });

        User::query()
            ->where('user_type', UserType::Secretary->value)
            ->each(function (User $user): void {
                if (! $user->hasRole(ChurchStaffRole::Secretary->value)) {
                    $user->assignRole(ChurchStaffRole::Secretary->value);
                }
            });

        User::query()
            ->where('user_type', UserType::Treasurer->value)
            ->each(function (User $user): void {
                if (! $user->hasRole(ChurchStaffRole::Treasurer->value)) {
                    $user->assignRole(ChurchStaffRole::Treasurer->value);
                }
            });

        User::query()
            ->where('user_type', UserType::Accountant->value)
            ->each(function (User $user): void {
                if (! $user->hasRole(ChurchStaffRole::Accountant->value)) {
                    $user->assignRole(ChurchStaffRole::Accountant->value);
                }
            });
    }
}
