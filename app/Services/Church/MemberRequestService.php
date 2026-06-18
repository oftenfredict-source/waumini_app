<?php

namespace App\Services\Church;

use App\Enums\LeadershipPosition;
use App\Enums\MemberRequestStatus;
use App\Enums\MemberRequestType;
use App\Models\Church;
use App\Models\Leader;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MemberRequestService
{
    /** @return list<LeadershipPosition> */
    public function assignablePositions(): array
    {
        return [
            LeadershipPosition::Pastor,
            LeadershipPosition::AssistantPastor,
            LeadershipPosition::Secretary,
            LeadershipPosition::AssistantSecretary,
            LeadershipPosition::Treasurer,
            LeadershipPosition::AssistantTreasurer,
            LeadershipPosition::Elder,
        ];
    }

    /**
     * @return Collection<int, Leader>
     */
    public function assignableLeaders(Church $church, ?int $branchId = null): Collection
    {
        $positions = array_map(fn (LeadershipPosition $p) => $p->value, $this->assignablePositions());

        return Leader::forChurch($church->id)
            ->with('member')
            ->whereIn('position', $positions)
            ->when($branchId, fn ($query) => $query->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            }))
            ->get()
            ->filter(fn (Leader $leader) => $leader->isCurrentlyActive())
            ->sortBy(fn (Leader $leader) => $leader->positionLabel())
            ->values();
    }

    /**
     * @param  array{type: string, subject: string, description?: string|null, assigned_leader_id: int, baptism_scope?: string, preferred_baptism_date?: string|null, child_dependant_ids?: array<int, int>}  $data
     */
    public function create(Member $member, array $data): MemberRequest
    {
        $leader = Leader::forChurch($member->church_id)
            ->whereKey($data['assigned_leader_id'])
            ->firstOrFail();

        abort_unless($this->isLeaderAssignable($leader), 422, 'Selected leader cannot receive member requests.');

        $type = MemberRequestType::from($data['type']);
        $requestMeta = null;
        $description = trim((string) ($data['description'] ?? ''));

        if ($type === MemberRequestType::BaptismRequest) {
            [$requestMeta, $description] = $this->buildBaptismRequestPayload($member, $data, $description);
        }

        return MemberRequest::create([
            'church_id' => $member->church_id,
            'member_id' => $member->id,
            'branch_id' => $member->branch_id,
            'assigned_leader_id' => $leader->id,
            'reference_number' => $this->nextReferenceNumber($member->church_id),
            'type' => $type,
            'subject' => $data['subject'],
            'description' => $description,
            'request_meta' => $requestMeta,
            'status' => MemberRequestStatus::Pending,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function buildBaptismRequestPayload(Member $member, array $data, string $extraNotes): array
    {
        $scope = (string) ($data['baptism_scope'] ?? 'self');
        $candidates = [];

        if (in_array($scope, ['self', 'both'], true)) {
            $candidates[] = [
                'name' => $member->full_name,
                'relationship' => 'self',
                'member_id' => $member->id,
            ];
        }

        $childIds = $data['child_dependant_ids'] ?? [];

        if (in_array($scope, ['children', 'both'], true) && is_array($childIds)) {
            $children = \App\Models\MemberDependant::query()
                ->whereIn('id', $childIds)
                ->where('church_id', $member->church_id)
                ->whereIn('member_id', $member->familyMemberIds())
                ->where('relationship', \App\Enums\DependantRelationship::Child)
                ->get();

            foreach ($children as $child) {
                $candidates[] = [
                    'name' => $child->full_name,
                    'relationship' => 'child',
                    'dependant_id' => $child->id,
                    'date_of_birth' => $child->date_of_birth?->toDateString(),
                ];
            }
        }

        $meta = [
            'baptism_scope' => $scope,
            'preferred_baptism_date' => $data['preferred_baptism_date'] ?? null,
            'candidates' => $candidates,
        ];

        $lines = ['Baptism request for:'];
        foreach ($candidates as $candidate) {
            $label = $candidate['relationship'] === 'self' ? 'Member (self)' : 'Child';
            $line = "- {$candidate['name']} ({$label})";
            if (! empty($candidate['date_of_birth'])) {
                $line .= ' — DOB: '.$candidate['date_of_birth'];
            }
            $lines[] = $line;
        }

        if (! empty($meta['preferred_baptism_date'])) {
            $lines[] = 'Preferred baptism date: '.$meta['preferred_baptism_date'];
        }

        if ($extraNotes !== '') {
            $lines[] = '';
            $lines[] = 'Additional notes:';
            $lines[] = $extraNotes;
        }

        return [$meta, implode("\n", $lines)];
    }

    public function respond(MemberRequest $request, User $user, string $status, ?string $response = null): MemberRequest
    {
        $request->update([
            'status' => MemberRequestStatus::from($status),
            'response' => $response,
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        $request = $request->fresh(['member', 'assignedLeader.member', 'responder', 'church']);

        if (app(MemberRequestCertificateService::class)->isEligible($request)) {
            try {
                app(MemberRequestCertificateService::class)->generate($request);
                $request->refresh();
            } catch (\Throwable $e) {
                Log::warning('Member request certificate generation failed', [
                    'member_request_id' => $request->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $request;
    }

    public function markInReview(MemberRequest $request, User $user): MemberRequest
    {
        if ($request->status === MemberRequestStatus::Pending) {
            $request->update(['status' => MemberRequestStatus::InReview]);
        }

        return $request->fresh(['member', 'assignedLeader.member', 'responder']);
    }

    public function isLeaderAssignable(Leader $leader): bool
    {
        if (! $leader->isCurrentlyActive()) {
            return false;
        }

        return in_array($leader->position, $this->assignablePositions(), true);
    }

    public function userHandlesRequest(User $user, MemberRequest $request): bool
    {
        if (! $user->member_id || ! $request->assigned_leader_id) {
            return false;
        }

        return Leader::query()
            ->whereKey($request->assigned_leader_id)
            ->where('member_id', $user->member_id)
            ->where('church_id', $request->church_id)
            ->exists();
    }

    private function nextReferenceNumber(int $churchId): string
    {
        $year = now()->format('Y');
        $prefix = "REQ-{$year}-";

        $latest = MemberRequest::forChurch($churchId)
            ->where('reference_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('reference_number');

        $sequence = 1;

        if ($latest && preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
