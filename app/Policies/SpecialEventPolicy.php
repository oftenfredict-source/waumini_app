<?php

namespace App\Policies;

use App\Models\SpecialEvent;
use App\Models\User;

class SpecialEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('special_events.view');
    }

    public function view(User $user, SpecialEvent $specialEvent): bool
    {
        return $user->isChurchUser()
            && $user->can('special_events.view')
            && $specialEvent->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('special_events.manage');
    }

    public function update(User $user, SpecialEvent $specialEvent): bool
    {
        return $user->isChurchUser()
            && $user->can('special_events.manage')
            && $specialEvent->church_id === $user->church_id;
    }

    public function delete(User $user, SpecialEvent $specialEvent): bool
    {
        return $this->update($user, $specialEvent);
    }
}
