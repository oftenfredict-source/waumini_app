<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Services\Church\BranchAccessService;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('departments.view');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->isChurchUser()
            && $user->can('departments.view')
            && $department->church_id === $user->church_id
            && app(BranchAccessService::class)->canAccessBranchId($user, $department->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('departments.manage');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->isChurchUser()
            && $user->can('departments.manage')
            && $department->church_id === $user->church_id
            && app(BranchAccessService::class)->canAccessBranchId($user, $department->branch_id);
    }

    public function delete(User $user, Department $department): bool
    {
        return $this->update($user, $department);
    }
}
