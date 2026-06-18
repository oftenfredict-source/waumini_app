<?php

namespace App\Policies;

use App\Models\Leader;
use App\Models\User;

class LeaderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('leadership.view');
    }

    public function view(User $user, Leader $leader): bool
    {
        return $user->isChurchUser()
            && $user->can('leadership.view')
            && $leader->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('leadership.manage');
    }

    public function update(User $user, Leader $leader): bool
    {
        return $user->isChurchUser()
            && $user->can('leadership.manage')
            && $leader->church_id === $user->church_id;
    }

    public function deactivate(User $user, Leader $leader): bool
    {
        return $this->update($user, $leader);
    }
}
