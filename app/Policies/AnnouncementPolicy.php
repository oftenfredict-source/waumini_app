<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('announcements.view');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->isChurchUser()
            && $user->can('announcements.view')
            && $announcement->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('announcements.manage');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->isChurchUser()
            && $user->can('announcements.manage')
            && $announcement->church_id === $user->church_id;
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->update($user, $announcement);
    }
}
