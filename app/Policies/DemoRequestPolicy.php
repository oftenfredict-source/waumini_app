<?php

namespace App\Policies;

use App\Models\DemoRequest;
use App\Models\User;

class DemoRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('churches.view');
    }

    public function view(User $user, DemoRequest $demoRequest): bool
    {
        return $user->can('churches.view');
    }

    public function approve(User $user, DemoRequest $demoRequest): bool
    {
        return $user->can('churches.create');
    }

    public function reject(User $user, DemoRequest $demoRequest): bool
    {
        return $user->can('churches.create');
    }
}
