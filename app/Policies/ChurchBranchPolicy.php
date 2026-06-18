<?php

namespace App\Policies;

use App\Models\ChurchBranch;
use App\Models\User;
use App\Services\Church\BranchAccessService;

class ChurchBranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isChurchUser()
            && $user->church?->branchesEnabled()
            && $user->can('branches.view');
    }

    public function view(User $user, ChurchBranch $branch): bool
    {
        return $user->isChurchUser()
            && $user->church?->branchesEnabled()
            && $user->can('branches.view')
            && app(BranchAccessService::class)->canAccessBranch($user, $branch);
    }

    public function create(User $user): bool
    {
        return $user->isChurchUser()
            && $user->church?->branchesEnabled()
            && $user->can('branches.manage')
            && app(BranchAccessService::class)->managesAllBranches($user);
    }

    public function update(User $user, ChurchBranch $branch): bool
    {
        return $user->isChurchUser()
            && $user->church?->branchesEnabled()
            && $user->can('branches.manage')
            && app(BranchAccessService::class)->canAccessBranch($user, $branch)
            && app(BranchAccessService::class)->managesAllBranches($user);
    }
}
