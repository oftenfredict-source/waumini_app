<?php

namespace App\Policies;

use App\Enums\BereavementStatus;
use App\Models\BereavementEvent;
use App\Models\User;

class BereavementEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('bereavements.view');
    }

    public function view(User $user, BereavementEvent $bereavementEvent): bool
    {
        return $user->isChurchUser()
            && $user->can('bereavements.view')
            && $bereavementEvent->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('bereavements.manage');
    }

    public function update(User $user, BereavementEvent $bereavementEvent): bool
    {
        return $user->isChurchUser()
            && $user->can('bereavements.manage')
            && $bereavementEvent->church_id === $user->church_id
            && $bereavementEvent->status === BereavementStatus::Open;
    }

    public function delete(User $user, BereavementEvent $bereavementEvent): bool
    {
        return $user->isChurchUser()
            && $user->can('bereavements.manage')
            && $bereavementEvent->church_id === $user->church_id;
    }

    public function manageContributions(User $user, BereavementEvent $bereavementEvent): bool
    {
        return $this->update($user, $bereavementEvent);
    }

    public function close(User $user, BereavementEvent $bereavementEvent): bool
    {
        return $user->isChurchUser()
            && $user->can('bereavements.manage')
            && $bereavementEvent->church_id === $user->church_id
            && $bereavementEvent->status === BereavementStatus::Open;
    }
}
