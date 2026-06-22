<?php

namespace App\Policies;

use App\Models\MemberRegistrationApplication;
use App\Models\User;

class MemberRegistrationApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('member_registrations.view');
    }

    public function view(User $user, MemberRegistrationApplication $application): bool
    {
        return $user->church_id === $application->church_id
            && $user->can('member_registrations.view');
    }

    public function review(User $user, MemberRegistrationApplication $application): bool
    {
        return $user->church_id === $application->church_id
            && $application->isPending()
            && $user->can('member_registrations.manage');
    }
}
