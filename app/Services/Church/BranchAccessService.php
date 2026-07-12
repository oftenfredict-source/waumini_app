<?php

namespace App\Services\Church;

use App\Enums\ChurchStaffRole;
use App\Models\ChurchBranch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BranchAccessService
{
    public const SESSION_KEY = 'church_active_branch_id';

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

    /**
     * Staff assignment or member's branch (ignores session).
     */
    public function assignedBranchId(User $user): ?int
    {
        return $user->branch_id ?? $user->member?->branch_id;
    }

    /**
     * Validated session branch for HQ/admin "enter branch" context.
     */
    public function sessionBranchId(User $user): ?int
    {
        if (! $this->branchesFeatureEnabled($user) || ! $this->managesAllBranches($user)) {
            return null;
        }

        $branchId = session(self::SESSION_KEY);

        if (! $branchId) {
            return null;
        }

        $branch = ChurchBranch::forChurch($user->church_id)
            ->whereKey($branchId)
            ->first();

        if (! $branch || ! $branch->is_active) {
            $this->exitBranch();

            return null;
        }

        return (int) $branch->id;
    }

    /**
     * Branch used for scoping lists and creates.
     * Assigned staff: their branch. HQ/admin: session when entered, else null (all branches).
     */
    public function effectiveBranchId(User $user): ?int
    {
        if (! $this->managesAllBranches($user)) {
            return $this->assignedBranchId($user);
        }

        return $this->sessionBranchId($user);
    }

    public function activeBranch(User $user): ?ChurchBranch
    {
        $branchId = $this->effectiveBranchId($user);

        if (! $branchId) {
            return null;
        }

        return ChurchBranch::forChurch($user->church_id)->whereKey($branchId)->first();
    }

    public function enterBranch(User $user, ChurchBranch $branch): void
    {
        abort_unless($this->canAccessBranch($user, $branch), 403);
        abort_unless($this->managesAllBranches($user), 403);
        abort_unless($branch->is_active, 422);

        session([self::SESSION_KEY => $branch->id]);
    }

    public function exitBranch(): void
    {
        session()->forget(self::SESSION_KEY);
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
            $this->constrainToBranch($query, $user, $branchId, $column);
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

        // Query-string filter only when viewing All branches (no session context).
        if (
            $this->managesAllBranches($user)
            && ! $this->sessionBranchId($user)
            && $requestedBranchId
        ) {
            $this->constrainToBranch($query, $user, $requestedBranchId, $column);
        }

        return $query;
    }

    /**
     * Whether the user may view/manage a record belonging to this branch.
     */
    public function canAccessBranch(User $user, ?ChurchBranch $branch): bool
    {
        if (! $branch || $branch->church_id !== $user->church_id) {
            return false;
        }

        if ($this->managesAllBranches($user)) {
            return true;
        }

        return $this->assignedBranchId($user) === $branch->id;
    }

    /**
     * Whether a record's branch_id is within the user's current scope.
     * HQ/admin in All branches: any. Entered/assigned: match (HQ also allows null).
     */
    public function canAccessBranchId(User $user, ?int $recordBranchId): bool
    {
        if (! $this->branchesFeatureEnabled($user)) {
            return true;
        }

        $effective = $this->effectiveBranchId($user);

        if (! $effective) {
            return true;
        }

        if ($recordBranchId === null) {
            $headquartersId = ChurchBranch::forChurch($user->church_id)
                ->where('is_headquarters', true)
                ->value('id');

            return $headquartersId && (int) $effective === (int) $headquartersId;
        }

        return (int) $recordBranchId === (int) $effective;
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
            $branchId = $this->assignedBranchId($user);

            if ($branchId) {
                $query->whereKey($branchId);
            }
        }

        return $query->get();
    }

    public function resolveBranchIdForCreate(User $user, ?int $requestedBranchId): ?int
    {
        if (! $this->managesAllBranches($user)) {
            return $this->assignedBranchId($user);
        }

        $sessionBranchId = $this->sessionBranchId($user);

        if ($sessionBranchId) {
            return $sessionBranchId;
        }

        return $requestedBranchId;
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    private function constrainToBranch(Builder $query, User $user, int $branchId, string $column): void
    {
        $headquartersId = ChurchBranch::forChurch($user->church_id)
            ->where('is_headquarters', true)
            ->value('id');

        if ($headquartersId && (int) $branchId === (int) $headquartersId) {
            $query->where(function (Builder $q) use ($column, $branchId) {
                $q->where($column, $branchId)
                    ->orWhereNull($column);
            });
        } else {
            $query->where($column, $branchId);
        }
    }
}
