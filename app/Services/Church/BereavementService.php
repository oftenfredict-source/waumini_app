<?php

namespace App\Services\Church;

use App\Enums\BereavementContributionType;
use App\Enums\BereavementStatus;
use App\Models\BereavementContribution;
use App\Models\BereavementEvent;
use App\Models\Church;
use App\Models\Member;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BereavementService
{
    public function create(Church $church, array $data, ?User $creator = null): BereavementEvent
    {
        return DB::transaction(function () use ($church, $data, $creator) {
            $memberIds = $data['member_ids'] ?? [];
            unset($data['member_ids']);

            $data['church_id'] = $church->id;
            $data['created_by'] = $creator?->id;
            $data['status'] = BereavementStatus::Open;

            $event = BereavementEvent::create($data);

            $members = $this->resolveMembersForTracking($church, $memberIds);
            $this->seedContributionRecords($event, $members, $creator);

            return $event->fresh(['contributions.member', 'creator', 'affectedMember']);
        });
    }

    public function update(BereavementEvent $event, array $data): BereavementEvent
    {
        unset($data['member_ids'], $data['status'], $data['closed_at']);

        $event->update($data);

        return $event->fresh();
    }

    public function delete(BereavementEvent $event): void
    {
        $event->delete();
    }

    public function recordContribution(
        BereavementEvent $event,
        array $data,
        ?User $recorder = null
    ): BereavementContribution {
        return BereavementContribution::updateOrCreate(
            [
                'bereavement_event_id' => $event->id,
                'member_id' => $data['member_id'],
            ],
            [
                'has_contributed' => true,
                'amount' => $data['amount'],
                'contribution_date' => $data['contribution_date'],
                'contribution_type' => $data['contribution_type'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $recorder?->id,
            ]
        )->load('member');
    }

    public function markNonContributor(
        BereavementEvent $event,
        int $memberId,
        ?User $recorder = null
    ): BereavementContribution {
        return BereavementContribution::updateOrCreate(
            [
                'bereavement_event_id' => $event->id,
                'member_id' => $memberId,
            ],
            [
                'has_contributed' => false,
                'amount' => null,
                'contribution_date' => null,
                'contribution_type' => BereavementContributionType::Individual,
                'payment_method' => null,
                'reference_number' => null,
                'notes' => null,
                'recorded_by' => $recorder?->id,
            ]
        )->load('member');
    }

    public function close(BereavementEvent $event, ?string $fundUsage = null): BereavementEvent
    {
        $event->close($fundUsage);

        return $event->fresh();
    }

    /**
     * @param  array<int>  $memberIds
     * @return \Illuminate\Support\Collection<int, Member>
     */
    private function resolveMembersForTracking(Church $church, array $memberIds)
    {
        $query = Member::forChurch($church->id)->where('status', 'active');

        if ($memberIds !== []) {
            $query->whereIn('id', $memberIds);
        }

        return $query->orderBy('full_name')->get();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Member>  $members
     */
    private function seedContributionRecords(
        BereavementEvent $event,
        $members,
        ?User $recorder = null
    ): void {
        foreach ($members as $member) {
            BereavementContribution::firstOrCreate(
                [
                    'bereavement_event_id' => $event->id,
                    'member_id' => $member->id,
                ],
                [
                    'has_contributed' => false,
                    'contribution_type' => BereavementContributionType::Individual,
                    'recorded_by' => $recorder?->id,
                ]
            );
        }
    }
}
