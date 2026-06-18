<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('dashboard.view');
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        return $user->can('dashboard.view');
    }
}
