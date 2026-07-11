<?php

namespace App\Http\Controllers\Church\System;

use App\Enums\ChurchStaffRole;
use App\Services\Church\ChurchRolePermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends SystemController
{
    public function __construct(
        private readonly ChurchRolePermissionService $rolePermissions,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.roles'), 403);

            return $next($request);
        });
    }

    public function index(): View
    {
        $church = $this->church();
        $this->rolePermissions->ensureDefaults($church);

        $roles = collect(ChurchStaffRole::configurableCases())->map(function (ChurchStaffRole $staffRole) use ($church) {
            $permissionNames = $this->rolePermissions->permissionNamesFor($church, $staffRole);

            return (object) [
                'name' => $staffRole->value,
                'label' => config('church.roles.'.$staffRole->value, $staffRole->label()),
                'permissions' => $permissionNames->map(fn (string $name) => (object) ['name' => $name]),
                'permission_names' => $permissionNames->all(),
            ];
        });

        $assignable = $this->rolePermissions->assignablePermissionNames();

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $assignable)
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0] ?? 'general');

        return view('church.system.roles.index', [
            'church' => $church,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $roleName = $request->string('role')->toString();
        $allowedRoles = array_map(fn (ChurchStaffRole $role) => $role->value, ChurchStaffRole::configurableCases());

        if (! in_array($roleName, $allowedRoles, true)) {
            abort(422, 'Invalid role selected.');
        }

        $allowedPermissions = $this->rolePermissions->assignablePermissionNames();

        $selected = collect($request->input('permissions', []))
            ->filter(fn ($name) => in_array($name, $allowedPermissions, true))
            ->values()
            ->all();

        $this->rolePermissions->sync($this->church(), $roleName, $selected);

        $roleLabel = config('church.roles.'.$roleName, ucfirst(str_replace('_', ' ', $roleName)));

        return back()->with('success', __('pages.system_roles.updated', ['role' => $roleLabel]));
    }
}
