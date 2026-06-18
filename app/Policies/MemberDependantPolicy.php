<?php

namespace App\Policies;

use App\Models\MemberDependant;
use App\Models\User;

class MemberDependantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('members.view');
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('members.update');
    }

    public function convert(User $user, MemberDependant $dependant): bool
    {
        return $user->isChurchUser()
            && $user->can('members.update')
            && $dependant->church_id === $user->church_id;
    }
}
