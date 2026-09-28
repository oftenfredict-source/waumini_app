<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('payments.view');
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can('payments.view');
    }

    public function create(User $user): bool
    {
        return $user->can('payments.manage');
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $user->can('payments.manage') && $invoice->isPending();
    }
}
