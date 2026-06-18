<?php

namespace App\Policies;

use App\Enums\ExpenseStatus;
use App\Enums\FinancialApprovalStatus;
use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.view');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.view')
            && $expense->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('finance.manage');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.manage')
            && $expense->church_id === $user->church_id
            && $expense->approval_status === FinancialApprovalStatus::Pending
            && ! $expense->isPaid();
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $this->update($user, $expense);
    }

    public function markPaid(User $user, Expense $expense): bool
    {
        return $user->isChurchUser()
            && $user->can('finance.manage')
            && $expense->church_id === $user->church_id
            && $expense->canBeMarkedPaid();
    }
}
