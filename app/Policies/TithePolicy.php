<?php

namespace App\Policies;

use App\Enums\FinancialApprovalStatus;
use App\Models\Tithe;
use App\Models\User;

class TithePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.view');
    }

    public function view(User $user, Tithe $tithe): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.view')
            && $tithe->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.manage');
    }

    public function update(User $user, Tithe $tithe): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.manage')
            && $tithe->church_id === $user->church_id
            && $tithe->approval_status === FinancialApprovalStatus::Pending;
    }

    public function delete(User $user, Tithe $tithe): bool
    {
        return $this->update($user, $tithe);
    }
}
