<?php

namespace App\Policies;

use App\Enums\FinancialApprovalStatus;
use App\Models\Offering;
use App\Models\User;

class OfferingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.view');
    }

    public function view(User $user, Offering $offering): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.view')
            && $offering->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.manage');
    }

    public function update(User $user, Offering $offering): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.manage')
            && $offering->church_id === $user->church_id
            && $offering->approval_status === FinancialApprovalStatus::Pending;
    }

    public function delete(User $user, Offering $offering): bool
    {
        return $this->update($user, $offering);
    }
}
