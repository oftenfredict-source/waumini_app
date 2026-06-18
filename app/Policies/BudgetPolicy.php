<?php

namespace App\Policies;

use App\Enums\FinancialApprovalStatus;
use App\Models\Budget;
use App\Models\User;

class BudgetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.view');
    }

    public function view(User $user, Budget $budget): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.view')
            && $budget->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.manage');
    }

    public function update(User $user, Budget $budget): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.manage')
            && $budget->church_id === $user->church_id
            && $budget->approval_status === FinancialApprovalStatus::Pending;
    }

    public function delete(User $user, Budget $budget): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.manage')
            && $budget->church_id === $user->church_id
            && $budget->approval_status === FinancialApprovalStatus::Pending
            && (float) $budget->spent_amount === 0.0;
    }

    public function allocateFunds(User $user, Budget $budget): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.manage')
            && $budget->church_id === $user->church_id
            && $budget->approval_status === FinancialApprovalStatus::Approved;
    }
}
