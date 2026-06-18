<?php

namespace App\Policies;

use App\Models\PromiseGuest;
use App\Models\User;

class PromiseGuestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('services.view');
    }

    public function view(User $user, PromiseGuest $promiseGuest): bool
    {
        return $user->isChurchUser()
            && $user->can('services.view')
            && $promiseGuest->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('services.manage');
    }

    public function update(User $user, PromiseGuest $promiseGuest): bool
    {
        return $user->isChurchUser()
            && $user->can('services.manage')
            && $promiseGuest->church_id === $user->church_id;
    }

    public function delete(User $user, PromiseGuest $promiseGuest): bool
    {
        return $this->update($user, $promiseGuest);
    }

    public function sendSms(User $user, PromiseGuest $promiseGuest): bool
    {
        return $this->update($user, $promiseGuest);
    }
}
