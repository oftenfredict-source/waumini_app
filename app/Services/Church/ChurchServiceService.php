<?php

namespace App\Services\Church;

use App\Enums\LeadershipPosition;
use App\Enums\ServiceCoordinatorType;
use App\Enums\ServicePreacherType;
use App\Models\Church;
use App\Models\ChurchBranch;
use App\Models\ChurchService;
use App\Models\Leader;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Collection;

class ChurchServiceService
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
    ) {}

    public function create(Church $church, array $data, ?User $creator = null): ChurchService
    {
        $data = $this->normalizeServiceData($church, $data);
        $data['church_id'] = $church->id;
        $data['created_by'] = $creator?->id;
        $data['branch_id'] = $this->resolveBranchId($church, $creator, $data['branch_id'] ?? null);

        return ChurchService::create($data);
    }

    public function update(ChurchService $service, array $data, ?User $actor = null): ChurchService
    {
        $data = $this->normalizeServiceData($service->church, $data);
        $actor ??= auth()->user();

        if ($actor && array_key_exists('branch_id', $data)) {
            if ($this->branchAccessService->managesAllBranches($actor)
                && ! $this->branchAccessService->sessionBranchId($actor)) {
                $data['branch_id'] = $this->resolveBranchId($service->church, $actor, $data['branch_id'] ?? null);
            } else {
                unset($data['branch_id']);
            }
        }

        $service->update($data);

        return $service->fresh(['preacherMember', 'coordinatorMember']);
    }

    public function delete(ChurchService $service): void
    {
        $service->delete();
    }

    /**
     * @return Collection<int, array{id: int, name: string, member_number: string|null, phone: string|null, position: string|null}>
     */
    public function pastorsForChurch(Church $church): Collection
    {
        return $this->leadersForChurch($church, [
            LeadershipPosition::Pastor->value,
            LeadershipPosition::AssistantPastor->value,
        ]);
    }

    /**
     * @return Collection<int, array{id: int, name: string, member_number: string|null, phone: string|null, position: string|null}>
     */
    public function leadersForChurch(Church $church, ?array $positions = null): Collection
    {
        $query = Leader::forChurch($church->id)
            ->active()
            ->with('member:id,full_name,member_number,phone_number')
            ->whereHas('member', fn ($q) => $q->whereNull('deleted_at'));

        if ($positions !== null) {
            $query->whereIn('position', $positions);
        }

        return $query->get()
            ->filter(fn (Leader $leader) => $leader->member)
            ->unique(fn (Leader $leader) => $leader->member_id)
            ->values()
            ->map(function (Leader $leader) {
                return [
                    'id' => (int) $leader->member_id,
                    'name' => $leader->member->full_name,
                    'member_number' => $leader->member->member_number,
                    'phone' => $leader->member->phone_number,
                    'position' => $leader->positionLabel(),
                ];
            });
    }

    /**
     * @return Collection<int, array{id: int, name: string, member_number: string|null, phone: string|null, position: null}>
     */
    public function searchMembers(Church $church, string $query, int $limit = 20): Collection
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return collect();
        }

        return Member::forChurch($church->id)
            ->activeMembers()
            ->where(function ($q) use ($query) {
                $q->where('full_name', 'like', "%{$query}%")
                    ->orWhere('member_number', 'like', "%{$query}%")
                    ->orWhere('phone_number', 'like', "%{$query}%");
            })
            ->orderBy('full_name')
            ->limit($limit)
            ->get(['id', 'full_name', 'member_number', 'phone_number'])
            ->map(fn (Member $member) => [
                'id' => (int) $member->id,
                'name' => $member->full_name,
                'member_number' => $member->member_number,
                'phone' => $member->phone_number,
                'position' => null,
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeServiceData(Church $church, array $data): array
    {
        if (($data['service_type'] ?? null) !== 'extra') {
            $data['title'] = null;
        }

        $preacherType = $data['preacher_type'] ?? null;
        $coordinatorType = $data['coordinator_type'] ?? null;

        if (! $preacherType) {
            $data['preacher_type'] = null;
            $data['preacher_member_id'] = null;
            $data['preacher'] = null;
            $data['preacher_phone'] = null;
        } elseif ($preacherType === ServicePreacherType::Guest->value) {
            $data['preacher_member_id'] = null;
            $data['preacher'] = trim((string) ($data['preacher_guest_name'] ?? '')) ?: null;
            $data['preacher_phone'] = trim((string) ($data['preacher_guest_phone'] ?? '')) ?: null;
        } else {
            $member = Member::forChurch($church->id)->find($data['preacher_member_id'] ?? null);
            $data['preacher_member_id'] = $member?->id;
            $data['preacher'] = $member?->full_name;
            $data['preacher_phone'] = null;
        }

        if (! $coordinatorType) {
            $data['coordinator_type'] = null;
            $data['coordinator_member_id'] = null;
            $data['coordinator_name'] = null;
            $data['coordinator_phone'] = null;
        } elseif ($coordinatorType === ServiceCoordinatorType::Guest->value) {
            $data['coordinator_member_id'] = null;
            $data['coordinator_name'] = trim((string) ($data['coordinator_guest_name'] ?? '')) ?: null;
            $data['coordinator_phone'] = trim((string) ($data['coordinator_guest_phone'] ?? '')) ?: null;
        } else {
            $member = Member::forChurch($church->id)->find($data['coordinator_member_id'] ?? null);
            $data['coordinator_member_id'] = $member?->id;
            $data['coordinator_name'] = $member?->full_name;
            $data['coordinator_phone'] = null;
        }

        unset(
            $data['preacher_guest_name'],
            $data['preacher_guest_phone'],
            $data['coordinator_guest_name'],
            $data['coordinator_guest_phone'],
        );

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
