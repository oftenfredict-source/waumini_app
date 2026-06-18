<?php

namespace App\Services\Church;

use App\Models\Announcement;
use App\Models\ChurchService;
use App\Models\Leader;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Offering;
use App\Models\Tithe;

class MemberPortalService
{
    /**
     * @return array<string, mixed>
     */
    public function buildDashboard(Member $member): array
    {
        $churchId = $member->church_id;
        $yearStart = now()->startOfYear();
        $yearEnd = now()->endOfYear();

        $member->load(['church', 'departments', 'spouseMember']);

        return [
            'member' => $member,
            'announcements' => Announcement::forChurch($churchId)
                ->active()
                ->targetedForMember($member)
                ->orderByDesc('is_pinned')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
            'upcoming_services' => ChurchService::forChurch($churchId)
                ->whereDate('service_date', '>=', now()->toDateString())
                ->orderBy('service_date')
                ->limit(5)
                ->get(),
            'leaders' => Leader::forChurch($churchId)
                ->with('member')
                ->get()
                ->filter(fn (Leader $leader) => $leader->isCurrentlyActive())
                ->sortBy(fn (Leader $leader) => $leader->positionLabel())
                ->values()
                ->take(8),
            'giving' => [
                'tithes_year' => (float) Tithe::forChurch($churchId)
                    ->where('member_id', $member->id)
                    ->approved()
                    ->whereBetween('tithe_date', [$yearStart, $yearEnd])
                    ->sum('amount'),
                'offerings_year' => (float) Offering::forChurch($churchId)
                    ->where('member_id', $member->id)
                    ->approved()
                    ->whereBetween('offering_date', [$yearStart, $yearEnd])
                    ->sum('amount'),
            ],
            'open_requests' => MemberRequest::forChurch($churchId)
                ->where('member_id', $member->id)
                ->whereIn('status', [
                    \App\Enums\MemberRequestStatus::Pending->value,
                    \App\Enums\MemberRequestStatus::InReview->value,
                ])
                ->count(),
            'recent_requests' => MemberRequest::forChurch($churchId)
                ->where('member_id', $member->id)
                ->with(['assignedLeader.member'])
                ->latest()
                ->limit(3)
                ->get(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Announcement>
     */
    public function announcementsFor(Member $member)
    {
        return Announcement::forChurch($member->church_id)
            ->active()
            ->targetedForMember($member)
            ->with(['creator', 'department'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, Leader>
     */
    public function activeLeaders(int $churchId)
    {
        return Leader::forChurch($churchId)
            ->with('member')
            ->get()
            ->filter(fn (Leader $leader) => $leader->isCurrentlyActive())
            ->sortBy(fn (Leader $leader) => $leader->positionLabel())
            ->values();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ChurchService>
     */
    public function upcomingServices(int $churchId)
    {
        return ChurchService::forChurch($churchId)
            ->whereDate('service_date', '>=', now()->toDateString())
            ->orderBy('service_date')
            ->orderBy('start_time')
            ->get();
    }
}
