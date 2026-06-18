<?php

namespace App\Policies;

use App\Models\SubscriptionPackage;
use App\Models\User;

class SubscriptionPackagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('subscriptions.view');
    }

    public function view(User $user, SubscriptionPackage $package): bool
    {
        return $user->can('subscriptions.view');
    }

    public function create(User $user): bool
    {
        return $user->can('subscriptions.manage');
    }

    public function update(User $user, SubscriptionPackage $package): bool
    {
        return $user->can('subscriptions.manage');
    }

    public function delete(User $user, SubscriptionPackage $package): bool
    {
        return $user->can('subscriptions.manage');
    }
}
