<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('members.view');
    }

    public function view(User $user, Member $member): bool
    {
        if ($member->church_id !== $user->church_id) {
            return false;
        }

        if ($user->hasLinkedMember()) {
            return $user->member_id === $member->id
                || $user->member?->spouse_member_id === $member->id;
        }

        return $user->isChurchUser() && $user->can('members.view');
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('members.create');
    }

    public function update(User $user, Member $member): bool
    {
        return $user->isChurchUser()
            && $user->can('members.update')
            && $member->church_id === $user->church_id;
    }

    public function delete(User $user, Member $member): bool
    {
        return $user->isChurchUser()
            && $user->can('members.delete')
            && $member->church_id === $user->church_id;
    }

    public function archive(User $user, Member $member): bool
    {
        return $this->update($user, $member);
    }

    public function restore(User $user, Member $member): bool
    {
        return $this->update($user, $member);
    }

    public function resetPassword(User $user, Member $member): bool
    {
        return $member->church_id === $user->church_id
            && $user->canManageMemberPasswords();
    }

    public function updateOwnProfile(User $user, Member $member): bool
    {
        return $user->canAccessMemberPortal()
            && $user->member_id === $member->id
            && $member->church_id === $user->church_id;
    }
}
