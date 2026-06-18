<?php

namespace App\Services\Church;

use App\Enums\ChurchStaffRole;
use App\Enums\UserType;
use App\Models\Leader;
use App\Models\Member;
use App\Models\User;

class LeadershipStaffAccessService
{
    public function refreshMemberAccess(Member $member): void
    {
        $user = User::query()
            ->where('church_id', $member->church_id)
            ->where('member_id', $member->id)
            ->first();

        if (! $user || $user->user_type === UserType::ChurchAdmin) {
            return;
        }

        $staffRole = $this->resolveStaffRoleForMember($member);

        if ($staffRole) {
            $user->update(['user_type' => $staffRole->userType()]);
            $user->syncRoles([$staffRole->value]);

            return;
        }

        $user->update(['user_type' => UserType::Member]);
        $user->syncRoles(['member']);
    }

    public function refreshForLeader(Leader $leader): void
    {
        if (! $leader->member_id) {
            return;
        }

        $member = Member::query()->find($leader->member_id);

        if ($member) {
            $this->refreshMemberAccess($member);
        }
    }

    public function syncAll(): int
    {
        $updated = 0;

        Member::query()
            ->whereHas('user')
            ->whereHas('leaders', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->chunkById(100, function ($members) use (&$updated) {
                foreach ($members as $member) {
                    $beforeType = $member->user?->user_type;
                    $this->refreshMemberAccess($member);
                    $member->user?->refresh();

                    if ($member->user && $beforeType !== $member->user->user_type) {
                        $updated++;
                    }
                }
            });

        return $updated;
    }

    private function resolveStaffRoleForMember(Member $member): ?ChurchStaffRole
    {
        $activeRoleValues = Leader::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->get()
            ->filter(fn (Leader $leader) => $leader->isCurrentlyActive())
            ->map(fn (Leader $leader) => ChurchStaffRole::fromLeadershipPosition($leader->position)?->value)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($activeRoleValues === []) {
            return null;
        }

        foreach (ChurchStaffRole::leadershipPriority() as $priorityRole) {
            if (in_array($priorityRole->value, $activeRoleValues, true)) {
                return $priorityRole;
            }
        }

        return null;
    }
}
