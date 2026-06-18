<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\Leader;
use App\Models\Member;
use App\Services\Sms\ChurchSmsService;
use Illuminate\Validation\ValidationException;

class LeaderService
{
    public function __construct(
        private readonly LeadershipStaffAccessService $leadershipStaffAccessService,
        private readonly ChurchSmsService $churchSmsService,
    ) {}

    public function assign(Church $church, array $data): Leader
    {
        $member = Member::forChurch($church->id)->findOrFail($data['member_id']);

        $this->ensureNoDuplicateActivePosition($church, $member->id, $data['position']);

        $data['church_id'] = $church->id;
        $data['branch_id'] = $member->branch_id;
        $data['is_active'] = true;

        if (empty($data['appointed_by'])) {
            $data['appointed_by'] = auth()->user()->name;
        }

        $leader = Leader::create($data);

        $this->leadershipStaffAccessService->refreshForLeader($leader);

        $this->churchSmsService->sendLeaderAppointment(
            $church,
            $member,
            (string) $data['position'],
        );

        return $leader;
    }

    public function deactivate(Leader $leader): Leader
    {
        $leader->update([
            'is_active' => false,
            'end_date' => $leader->end_date ?? now()->toDateString(),
        ]);

        $leader = $leader->fresh(['member']);

        $this->leadershipStaffAccessService->refreshForLeader($leader);

        return $leader;
    }

    private function ensureNoDuplicateActivePosition(Church $church, int $memberId, string $position, ?int $exceptId = null): void
    {
        $query = Leader::forChurch($church->id)
            ->where('member_id', $memberId)
            ->where('position', $position)
            ->where('is_active', true);

        if ($exceptId) {
            $query->whereKeyNot($exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'position' => 'This member already holds an active assignment for this position.',
            ]);
        }
    }
}
