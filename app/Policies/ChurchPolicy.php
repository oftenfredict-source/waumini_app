<?php

namespace App\Policies;

use App\Models\Church;
use App\Models\User;

class ChurchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('churches.view');
    }

    public function view(User $user, Church $church): bool
    {
        return $user->can('churches.view');
    }

    public function create(User $user): bool
    {
        return $user->can('churches.create');
    }

    public function update(User $user, Church $church): bool
    {
        return $user->can('churches.update');
    }

    public function delete(User $user, Church $church): bool
    {
        return $user->can('churches.delete');
    }

    public function suspend(User $user, Church $church): bool
    {
        return $user->can('churches.suspend');
    }

    public function activate(User $user, Church $church): bool
    {
        return $user->can('churches.activate');
    }

    public function manageAdmin(User $user, Church $church): bool
    {
        return $user->can('churches.update');
    }
}
