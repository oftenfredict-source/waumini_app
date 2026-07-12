<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\ChurchBranch;
use App\Models\SpecialEvent;
use App\Models\User;

class SpecialEventService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
    ) {}

    public function create(Church $church, array $data, ?User $creator = null): SpecialEvent
    {
        $data['church_id'] = $church->id;
        $data['created_by'] = $creator?->id;
        $data = $this->normalizeCategoryFields($data);
        $data['branch_id'] = $this->resolveBranchId($church, $creator, $data['branch_id'] ?? null);

        return SpecialEvent::create($data);
    }

    public function update(SpecialEvent $event, array $data, ?User $actor = null): SpecialEvent
    {
        $data = $this->normalizeCategoryFields($data);
        $actor ??= auth()->user();

        if ($actor && array_key_exists('branch_id', $data)) {
            if ($this->branchAccessService->managesAllBranches($actor)
                && ! $this->branchAccessService->sessionBranchId($actor)) {
                $data['branch_id'] = $this->resolveBranchId($event->church, $actor, $data['branch_id'] ?? null);
            } else {
                unset($data['branch_id']);
            }
        }

        $event->update($data);

        return $event->fresh();
    }

    public function delete(SpecialEvent $event): void
    {
        $event->delete();
    }

    private function normalizeCategoryFields(array $data): array
    {
        if (($data['category'] ?? null) !== 'other') {
            $data['category_other'] = null;
        }

        return $data;
    }

    private function resolveBranchId(Church $church, ?User $user, mixed $requestedBranchId): ?int
    {
        if (! $user || ! $this->branchAccessService->branchesFeatureEnabled($user)) {
            return $requestedBranchId ? (int) $requestedBranchId : null;
        }

        $branchId = $this->branchAccessService->resolveBranchIdForCreate(
            $user,
            $requestedBranchId ? (int) $requestedBranchId : null,
        );

        if ($branchId) {
            return $branchId;
        }

        return ChurchBranch::forChurch($church->id)
            ->where('is_headquarters', true)
            ->value('id');
    }
}
