<?php

namespace App\Policies;

use App\Models\OwnerFile;
use App\Models\User;

class OwnerFilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('files.view');
    }

    public function view(User $user, OwnerFile $file): bool
    {
        return $user->can('files.view');
    }

    public function create(User $user): bool
    {
        return $user->can('files.manage');
    }

    public function delete(User $user, OwnerFile $file): bool
    {
        return $user->can('files.manage');
    }

    public function download(User $user, OwnerFile $file): bool
    {
        return $user->can('files.view');
    }
}
