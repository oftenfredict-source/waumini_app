<?php

namespace App\Policies;

use App\Models\Celebration;
use App\Models\User;

class CelebrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('services.view');
    }

    public function view(User $user, Celebration $celebration): bool
    {
        return $user->isChurchUser()
            && $user->can('services.view')
            && $celebration->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('services.manage');
    }

    public function update(User $user, Celebration $celebration): bool
    {
        return $user->isChurchUser()
            && $user->can('services.manage')
            && $celebration->church_id === $user->church_id;
    }

    public function delete(User $user, Celebration $celebration): bool
    {
        return $this->update($user, $celebration);
    }
}
