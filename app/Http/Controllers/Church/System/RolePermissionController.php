<?php

namespace App\Http\Controllers\Church\System;

use App\Enums\ChurchStaffRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends SystemController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.roles'), 403);

            return $next($request);
        });
    }

    public function index(): View
    {
        $churchRoles = collect(ChurchStaffRole::cases())
            ->map(fn (ChurchStaffRole $role) => Role::query()
                ->where('name', $role->value)
                ->with('permissions')
                ->first())
            ->filter();

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0] ?? 'general');

        return view('church.system.roles.index', [
            'church' => $this->church(),
            'roles' => $churchRoles,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $roleName = $request->string('role')->toString();
        $allowedRoles = array_map(fn (ChurchStaffRole $role) => $role->value, ChurchStaffRole::cases());

        if (! in_array($roleName, $allowedRoles, true)) {
            abort(422, 'Invalid role selected.');
        }

        $permissionNames = Permission::query()
            ->where('guard_name', 'web')
            ->pluck('name')
            ->all();

        $selected = collect($request->input('permissions', []))
            ->filter(fn ($name) => in_array($name, $permissionNames, true))
            ->values()
            ->all();

        $role = Role::where('name', $roleName)->firstOrFail();
        $role->syncPermissions($selected);

        $roleLabel = config('church.roles.'.$roleName, ucfirst($roleName));

        return back()->with('success', 'Permissions updated for '.$roleLabel.'.');
    }
}
