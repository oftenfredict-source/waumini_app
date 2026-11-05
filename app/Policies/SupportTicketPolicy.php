<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isOwnerUser()) {
            return $user->can('dashboard.view');
        }

        return $this->isChurchStaff($user);
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        if ($user->isOwnerUser()) {
            return $user->can('dashboard.view');
        }

        return $this->isChurchStaff($user) && (int) $user->church_id === (int) $ticket->church_id;
    }

    public function create(User $user): bool
    {
        return $this->isChurchStaff($user);
    }

    public function update(User $user, SupportTicket $ticket): bool
    {
        return $user->isOwnerUser() && $user->can('dashboard.view');
    }

    private function isChurchStaff(User $user): bool
    {
        return $user->isChurchUser();
    }
}
