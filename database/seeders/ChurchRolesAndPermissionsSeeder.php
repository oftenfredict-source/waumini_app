<?php

namespace Database\Seeders;

use App\Enums\ChurchStaffRole;
use App\Enums\UserType;
use App\Models\User;
use App\Services\Church\ChurchRolePermissionService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class ChurchRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissionService = app(ChurchRolePermissionService::class);
        $permissions = $permissionService->assignablePermissionNames();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $allChurchPermissions = Permission::whereIn('name', $permissions)->get();

        foreach (ChurchStaffRole::cases() as $staffRole) {
            $role = Role::firstOrCreate(['name' => $staffRole->value, 'guard_name' => 'web']);
            $role->syncPermissions($permissionService->defaultPermissionNamesFor($staffRole));
        }

        Role::firstOrCreate(['name' => 'church_admin', 'guard_name' => 'web'])
            ->syncPermissions($allChurchPermissions);

        Role::firstOrCreate(['name' => 'member', 'guard_name' => 'web']);

        $this->assignRoleToUsers(UserType::ChurchAdmin, ChurchStaffRole::Administrator);
        $this->assignRoleToUsers(UserType::Pastor, ChurchStaffRole::Pastor);
        $this->assignRoleToUsers(UserType::AssistantPastor, ChurchStaffRole::AssistantPastor);
        $this->assignRoleToUsers(UserType::Elder, ChurchStaffRole::Elder);
        $this->assignRoleToUsers(UserType::Secretary, ChurchStaffRole::Secretary);
        $this->assignRoleToUsers(UserType::Treasurer, ChurchStaffRole::Treasurer);
        $this->assignRoleToUsers(UserType::Accountant, ChurchStaffRole::Accountant);
    }

    private function assignRoleToUsers(UserType $userType, ChurchStaffRole $role): void
    {
        User::query()
            ->where('user_type', $userType->value)
            ->each(function (User $user) use ($role): void {
                if (! $user->hasRole($role->value)) {
                    $user->syncRoles([$role->value]);
                }
            });
    }
}
