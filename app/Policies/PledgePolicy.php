<?php

namespace App\Policies;

use App\Enums\PledgeStatus;
use App\Models\Pledge;
use App\Models\User;

class PledgePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.view');
    }

    public function view(User $user, Pledge $pledge): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.view')
            && $pledge->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.manage');
    }

    public function update(User $user, Pledge $pledge): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.manage')
            && $pledge->church_id === $user->church_id
            && in_array($pledge->status, [PledgeStatus::Active, PledgeStatus::Overdue], true);
    }

    public function delete(User $user, Pledge $pledge): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.manage')
            && $pledge->church_id === $user->church_id
            && (float) $pledge->amount_paid === 0.0;
    }

    public function recordPayment(User $user, Pledge $pledge): bool
    {
        return $this->update($user, $pledge) && ! $pledge->isCompleted();
    }
}
