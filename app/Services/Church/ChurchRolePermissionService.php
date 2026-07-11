<?php

namespace App\Services\Church;

use App\Enums\ChurchStaffRole;
use App\Enums\UserType;
use App\Models\Church;
use App\Models\ChurchRolePermission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ChurchRolePermissionService
{
    /**
     * Permissions church admins may assign to staff roles (excludes owner/platform).
     *
     * @return list<string>
     */
    public function assignablePermissionNames(): array
    {
        return array_values(config('church.assignable_permissions', []));
    }

    public function isAssignablePermission(string $permission): bool
    {
        return in_array($permission, $this->assignablePermissionNames(), true);
    }

    /**
     * Ensure each staff role has a church-scoped permission set (seeded from Spatie defaults once).
     */
    public function ensureDefaults(Church $church): void
    {
        $this->pruneNonAssignable($church);

        foreach (ChurchStaffRole::configurableCases() as $staffRole) {
            $exists = ChurchRolePermission::forChurch($church->id)
                ->forRole($staffRole->value)
                ->exists();

            if ($exists) {
                continue;
            }

            $defaults = $this->defaultPermissionNamesFor($staffRole);
            $this->sync($church, $staffRole, $defaults);
        }
    }

    /**
     * Remove owner/platform permissions that should never appear on church roles.
     */
    public function pruneNonAssignable(Church $church): int
    {
        return ChurchRolePermission::forChurch($church->id)
            ->whereNotIn('permission', $this->assignablePermissionNames())
            ->delete();
    }

    /**
     * @return Collection<int, string>
     */
    public function permissionNamesFor(Church $church, ChurchStaffRole|string $role): Collection
    {
        $roleName = $role instanceof ChurchStaffRole ? $role->value : $role;

        $this->ensureDefaults($church);

        return ChurchRolePermission::forChurch($church->id)
            ->forRole($roleName)
            ->whereIn('permission', $this->assignablePermissionNames())
            ->orderBy('permission')
            ->pluck('permission');
    }

    /**
     * @param  list<string>  $permissionNames
     */
    public function sync(Church $church, ChurchStaffRole|string $role, array $permissionNames): void
    {
        $roleName = $role instanceof ChurchStaffRole ? $role->value : $role;
        $allowed = $this->assignablePermissionNames();

        $selected = collect($permissionNames)
            ->filter(fn ($name) => in_array($name, $allowed, true))
            ->unique()
            ->values()
            ->all();

        DB::transaction(function () use ($church, $roleName, $selected) {
            ChurchRolePermission::forChurch($church->id)
                ->forRole($roleName)
                ->delete();

            $now = now();
            $rows = array_map(fn (string $permission) => [
                'church_id' => $church->id,
                'role' => $roleName,
                'permission' => $permission,
                'created_at' => $now,
                'updated_at' => $now,
            ], $selected);

            foreach (array_chunk($rows, 100) as $chunk) {
                ChurchRolePermission::query()->insert($chunk);
            }
        });
    }

    /**
     * Resolve a church staff user's permission via church-scoped role matrix.
     * Returns null to defer to Spatie/other gates.
     */
    public function userMay(User $user, string $ability): ?bool
    {
        if (! $user->church_id || ! str_contains($ability, '.')) {
            return null;
        }

        if (! in_array($user->user_type, UserType::churchStaffTypes(), true)) {
            return null;
        }

        // Owner/platform abilities are never granted through church roles.
        if (! $this->isAssignablePermission($ability)) {
            return false;
        }

        $staffRole = ChurchStaffRole::fromUserType($user->user_type);

        if (! $staffRole) {
            return null;
        }

        $church = $user->church ?? Church::query()->find($user->church_id);

        if (! $church) {
            return null;
        }

        $permissions = $this->permissionNamesFor($church, $staffRole);

        return $permissions->contains($ability);
    }

    /**
     * @return list<string>
     */
    public function defaultPermissionNamesFor(ChurchStaffRole $role): array
    {
        $spatieRole = Role::query()
            ->where('name', $role->value)
            ->where('guard_name', 'web')
            ->with('permissions')
            ->first();

        if ($spatieRole && $spatieRole->permissions->isNotEmpty()) {
            return $this->onlyAssignable($spatieRole->permissions->pluck('name')->all());
        }

        return match ($role) {
            ChurchStaffRole::Administrator => $this->assignablePermissionNames(),
            ChurchStaffRole::Pastor => $this->nonSystemPermissionNames(),
            ChurchStaffRole::AssistantPastor => $this->assistantPastorDefaultNames(),
            ChurchStaffRole::Elder => $this->elderDefaultNames(),
            ChurchStaffRole::Secretary => array_values(array_filter(
                $this->nonSystemPermissionNames(),
                fn (string $name) => ! str_starts_with($name, 'finance.')
            )),
            ChurchStaffRole::Treasurer => array_values(array_filter(
                $this->nonSystemPermissionNames(),
                fn (string $name) => str_starts_with($name, 'finance.')
                    || str_starts_with($name, 'assets.')
                    || in_array($name, [
                        'reports.view',
                        'analytics.view',
                        'members.view',
                        'bereavements.view',
                        'bereavements.manage',
                    ], true)
            )),
            ChurchStaffRole::Accountant => [
                'finance.view',
                'finance.manage',
                'assets.view',
                'assets.manage',
                'reports.view',
                'analytics.view',
                'members.view',
                'bereavements.view',
                'bereavements.manage',
            ],
        };
    }

    /**
     * @param  list<string>  $names
     * @return list<string>
     */
    private function onlyAssignable(array $names): array
    {
        $allowed = $this->assignablePermissionNames();

        return array_values(array_filter(
            $names,
            fn (string $name) => in_array($name, $allowed, true)
        ));
    }

    /**
     * @return list<string>
     */
    private function nonSystemPermissionNames(): array
    {
        return array_values(array_filter(
            $this->assignablePermissionNames(),
            fn (string $name) => ! str_starts_with($name, 'system.')
        ));
    }

    /**
     * @return list<string>
     */
    private function elderDefaultNames(): array
    {
        return [
            'members.view',
            'leadership.view',
            'departments.view',
            'announcements.view',
            'services.view',
            'services.manage',
            'special_events.view',
            'attendance.view',
            'attendance.manage',
            'bereavements.view',
            'member_requests.view',
            'reports.view',
        ];
    }

    /**
     * @return list<string>
     */
    private function assistantPastorDefaultNames(): array
    {
        return array_values(array_filter(
            $this->nonSystemPermissionNames(),
            fn (string $name) => ! in_array($name, [
                'finance.approve',
                'branches.manage',
            ], true)
        ));
    }
}
