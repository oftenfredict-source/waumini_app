<?php

namespace App\Services\Church;

use App\Enums\AttendanceSourceType;
use App\Enums\DepartmentStatus;
use App\Enums\SpecialEventStatus;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\ChurchService;
use App\Models\Department;
use App\Models\Leader;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Offering;
use App\Models\Pledge;
use App\Models\SpecialEvent;
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
            ->with(['branch', 'preacherMember', 'coordinatorMember'])
            ->whereDate('service_date', '>=', now()->toDateString())
            ->orderBy('service_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * All created services for the member's church (past and upcoming).
     */
    public function servicesForChurch(int $churchId)
    {
        return ChurchService::forChurch($churchId)
            ->with(['branch', 'preacherMember', 'coordinatorMember'])
            ->orderByDesc('service_date')
            ->orderByDesc('start_time');
    }

    public function findServiceForChurch(int $churchId, int $id): ?ChurchService
    {
        return ChurchService::forChurch($churchId)
            ->with(['branch', 'preacherMember', 'coordinatorMember'])
            ->find($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function givingFor(Member $member): array
    {
        $member->loadMissing('church');

        $tithes = Tithe::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->orderByDesc('tithe_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Tithe $tithe) => [
                'id' => 'tithe-'.$tithe->id,
                'type' => 'tithe',
                'amount' => (float) $tithe->amount,
                'date' => $tithe->tithe_date?->toDateString(),
                'status' => $tithe->approval_status?->value,
                'payment_method' => $tithe->payment_method?->label(),
                'reference' => $tithe->reference_number,
                'notes' => $tithe->notes,
            ]);

        $offerings = Offering::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->orderByDesc('offering_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Offering $offering) => [
                'id' => 'offering-'.$offering->id,
                'type' => 'offering',
                'label' => $offering->offeringTypeLabel(),
                'offering_type' => $offering->offering_type?->value,
                'amount' => (float) $offering->amount,
                'date' => $offering->offering_date?->toDateString(),
                'status' => $offering->approval_status?->value,
                'payment_method' => $offering->payment_method?->label(),
                'reference' => $offering->reference_number,
                'notes' => $offering->notes,
            ]);

        $pledges = Pledge::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->orderByDesc('pledge_date')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Pledge $pledge) => $this->serializePledge($pledge));

        $items = $tithes->concat($offerings)->concat($pledges)
            ->sortByDesc(fn (array $item) => $item['date'] ?? '')
            ->values()
            ->all();

        $approvedTithes = (float) Tithe::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->approved()
            ->sum('amount');
        $approvedOfferings = (float) Offering::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->approved()
            ->sum('amount');
        $pledgesPaid = (float) Pledge::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->sum('amount_paid');

        return [
            'currency' => $member->church?->currency ?? 'TZS',
            'totals' => [
                'tithes' => $approvedTithes,
                'offerings' => $approvedOfferings,
                'pledges' => $pledgesPaid,
                'all' => $approvedTithes + $approvedOfferings + $pledgesPaid,
            ],
            'items' => $items,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function departmentsFor(Member $member): array
    {
        $member->loadMissing('departments');
        $mine = $member->departments->keyBy('id');

        return Department::forChurch($member->church_id)
            ->with(['head', 'branch'])
            ->where('status', DepartmentStatus::Active)
            ->orderBy('name')
            ->get()
            ->map(function (Department $department) use ($mine) {
                $membership = $mine->get($department->id);

                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'description' => $department->description,
                    'head_name' => $department->head?->full_name,
                    'branch_name' => $department->branch?->displayLabel(),
                    'is_member' => $membership !== null,
                    'role' => $membership?->pivot?->role,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function eventsFor(Member $member): array
    {
        return SpecialEvent::forChurch($member->church_id)
            ->with('branch')
            ->where('status', '!=', SpecialEventStatus::Cancelled->value)
            ->orderByRaw("CASE WHEN status = ? THEN 0 ELSE 1 END", [SpecialEventStatus::Scheduled->value])
            ->orderBy('event_date')
            ->orderBy('start_time')
            ->limit(50)
            ->get()
            ->map(fn (SpecialEvent $event) => [
                'id' => $event->id,
                'title' => $event->title,
                'category_label' => $event->categoryLabel(),
                'event_date' => $event->event_date?->toDateString(),
                'start_time' => $event->start_time ? substr((string) $event->start_time, 0, 5) : null,
                'end_time' => $event->end_time ? substr((string) $event->end_time, 0, 5) : null,
                'venue' => $event->venue,
                'speaker' => $event->speaker,
                'status' => $event->status?->value,
                'description' => $event->description,
                'notes' => $event->notes,
                'branch_name' => $event->branch?->displayLabel(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function attendanceFor(Member $member): array
    {
        $records = AttendanceRecord::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->orderByDesc('attended_at')
            ->limit(50)
            ->get();

        $serviceIds = $records
            ->where('source_type', AttendanceSourceType::ChurchService)
            ->pluck('source_id');
        $eventIds = $records
            ->where('source_type', AttendanceSourceType::SpecialEvent)
            ->pluck('source_id');

        $services = ChurchService::query()->whereIn('id', $serviceIds)->get()->keyBy('id');
        $events = SpecialEvent::query()->whereIn('id', $eventIds)->get()->keyBy('id');

        return $records->map(function (AttendanceRecord $record) use ($services, $events) {
            $title = match ($record->source_type) {
                AttendanceSourceType::ChurchService => $services->get($record->source_id)?->displayTitle() ?? 'Ibada',
                AttendanceSourceType::SpecialEvent => $events->get($record->source_id)?->title ?? 'Tukio',
                default => 'Mahudhurio',
            };

            return [
                'id' => $record->id,
                'title' => $title,
                'source_type' => $record->source_type?->value,
                'source_label' => match ($record->source_type) {
                    AttendanceSourceType::ChurchService => 'Ibada',
                    AttendanceSourceType::SpecialEvent => 'Tukio',
                    default => 'Mahudhurio',
                },
                'status' => 'present',
                'attended_at' => $record->attended_at?->toIso8601String(),
                'attended_date' => $record->attended_at?->toDateString(),
                'attended_time' => $record->attended_at?->format('H:i'),
            ];
        })->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePledge(Pledge $pledge): array
    {
        return [
            'id' => 'pledge-'.$pledge->id,
            'pledge_id' => $pledge->id,
            'type' => 'pledge',
            'label' => $pledge->pledgeTypeLabel(),
            'pledge_type' => $pledge->pledge_type?->value,
            'amount' => (float) $pledge->amount_paid,
            'pledged_amount' => (float) $pledge->pledge_amount,
            'amount_paid' => (float) $pledge->amount_paid,
            'remaining_amount' => $pledge->remainingAmount(),
            'progress' => $pledge->progressPercentage(),
            'date' => $pledge->pledge_date?->toDateString(),
            'due_date' => $pledge->due_date?->toDateString(),
            'payment_frequency' => $pledge->payment_frequency?->value,
            'status' => $pledge->status?->value,
            'purpose' => $pledge->purpose,
            'notes' => $pledge->notes,
            'payment_method' => $pledge->pledgeTypeLabel(),
            'reference' => null,
        ];
    }
}
