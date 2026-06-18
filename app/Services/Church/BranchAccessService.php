<?php

namespace App\Services\Church;

use App\Enums\ChurchStaffRole;
use App\Models\ChurchBranch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BranchAccessService
{
    public function branchesFeatureEnabled(?User $user): bool
    {
        return (bool) ($user?->church?->branches_enabled);
    }

    public function managesAllBranches(User $user): bool
    {
        if (! $user->isChurchUser() || $user->isChurchMember()) {
            return false;
        }

        if ($user->isChurchAdmin() || $user->hasRole(ChurchStaffRole::Administrator->value)) {
            return true;
        }

        return $user->branch_id === null;
    }

    public function effectiveBranchId(User $user): ?int
    {
        if ($this->managesAllBranches($user)) {
            return null;
        }

        return $user->branch_id ?? $user->member?->branch_id;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function applyBranchScope(Builder $query, User $user, string $column = 'branch_id'): Builder
    {
        if (! $this->branchesFeatureEnabled($user)) {
            return $query;
        }

        $branchId = $this->effectiveBranchId($user);

        if ($branchId) {
            $query->where($column, $branchId);
        }

        return $query;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     * @return Builder<\Illuminate\Database\Eloquent\Model>
     */
    public function applyBranchFilter(Builder $query, User $user, ?int $requestedBranchId, string $column = 'branch_id'): Builder
    {
        $this->applyBranchScope($query, $user, $column);

        if ($this->managesAllBranches($user) && $requestedBranchId) {
            $query->where($column, $requestedBranchId);
        }

        return $query;
    }

    public function canAccessBranch(User $user, ?ChurchBranch $branch): bool
    {
        if (! $branch || $branch->church_id !== $user->church_id) {
            return false;
        }

        if ($this->managesAllBranches($user)) {
            return true;
        }

        return $this->effectiveBranchId($user) === $branch->id;
    }

    /**
     * @return Collection<int, ChurchBranch>
     */
    public function selectableBranches(User $user): Collection
    {
        if (! $this->branchesFeatureEnabled($user)) {
            return collect();
        }

        $query = ChurchBranch::forChurch($user->church_id)
            ->active()
            ->orderByDesc('is_headquarters')
            ->orderBy('name');

        if (! $this->managesAllBranches($user)) {
            $branchId = $this->effectiveBranchId($user);

            if ($branchId) {
                $query->whereKey($branchId);
            }
        }

        return $query->get();
    }

    public function resolveBranchIdForCreate(User $user, ?int $requestedBranchId): ?int
    {
        if ($this->managesAllBranches($user)) {
            return $requestedBranchId;
        }

        return $this->effectiveBranchId($user);
    }
}
