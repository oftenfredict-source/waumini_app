<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

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
            && $department->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('departments.manage');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->isChurchUser()
            && $user->can('departments.manage')
            && $department->church_id === $user->church_id;
    }

    public function delete(User $user, Department $department): bool
    {
        return $this->update($user, $department);
    }
}
