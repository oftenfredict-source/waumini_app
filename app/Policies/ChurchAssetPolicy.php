<?php

namespace App\Policies;

use App\Models\ChurchAsset;
use App\Models\User;

class ChurchAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser() && $user->can('assets.view');
    }

    public function view(User $user, ChurchAsset $asset): bool
    {
        return $user->isChurchUser()
            && $user->can('assets.view')
            && $asset->church_id === $user->church_id;
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser() && $user->can('assets.manage');
    }

    public function update(User $user, ChurchAsset $asset): bool
    {
        return $user->isChurchUser()
            && $user->can('assets.manage')
            && $asset->church_id === $user->church_id;
    }

    public function delete(User $user, ChurchAsset $asset): bool
    {
        return $this->update($user, $asset);
    }
}
