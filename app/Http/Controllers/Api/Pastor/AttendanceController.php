<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Enums\AttendanceSourceType;
use App\Enums\ChurchServiceStatus;
use App\Enums\SpecialEventStatus;
use App\Http\Requests\Church\StoreAttendanceRequest;
use App\Http\Resources\Api\AttendanceRecordResource;
use App\Models\AttendanceRecord;
use App\Models\ChurchService;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Models\SpecialEvent;
use App\Services\Church\AttendanceService;
use App\Services\Church\BranchAccessService;
use App\Services\Church\ChurchSettingsService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends PastorController
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly AttendanceService $attendanceService,
        private readonly ChurchSettingsService $churchSettingsService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $user = $this->pastor();
        $from = $request->date('from')?->startOfDay() ?? now()->startOfMonth();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();

        $query = AttendanceRecord::forChurch($user->church_id)
            ->with([
                'member:id,full_name,member_number',
                'dependant:id,full_name',
                'branch:id,name',
            ])
            ->whereBetween('attended_at', [$from, $to])
            ->latest('attended_at');

        if ($sourceType = trim($request->string('source_type')->toString())) {
            $query->where('source_type', $sourceType);
        }

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->integer('source_id'));
        }

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        $search = trim($request->string('search')->toString());
        if ($search !== '') {
            $query->whereHas('member', function ($member) use ($search) {
                $member->where('full_name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%");
            });
        }

        $records = $query->paginate($this->perPage($request));

        return $this->paginated($records, AttendanceRecordResource::collection($records)->resolve());
    }

    public function overview(Request $request): JsonResponse
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $user = $this->pastor();
        $records = AttendanceRecord::forChurch($user->church_id);
        $this->branchAccessService->applyBranchFilter(
            $records,
            $user,
            $request->integer('branch_id') ?: null,
        );

        $thisMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        return ApiResponse::success([
            'this_month' => $this->periodTotals($records, $thisMonthStart, now()->endOfDay()),
            'last_month' => $this->periodTotals($records, $lastMonthStart, $lastMonthEnd),
            'recent' => $this->recentSessions($request),
        ]);
    }

    public function sessions(Request $request): JsonResponse
    {
        $this->authorize('create', AttendanceRecord::class);

        $user = $this->pastor();
        $from = now()->subDay()->toDateString();
        $to = now()->addDays(14)->toDateString();

        $serviceQuery = ChurchService::forChurch($user->church_id)
            ->with('branch:id,name')
            ->whereBetween('service_date', [$from, $to])
            ->where('status', '!=', ChurchServiceStatus::Cancelled->value)
            ->orderBy('service_date')
            ->orderBy('start_time');
        $this->branchAccessService->applyBranchFilter(
            $serviceQuery,
            $user,
            $request->integer('branch_id') ?: null,
        );

        $eventQuery = SpecialEvent::forChurch($user->church_id)
            ->with('branch:id,name')
            ->whereBetween('event_date', [$from, $to])
            ->where('status', '!=', SpecialEventStatus::Cancelled->value)
            ->orderBy('event_date')
            ->orderBy('start_time');
        $this->branchAccessService->applyBranchFilter(
            $eventQuery,
            $user,
            $request->integer('branch_id') ?: null,
        );

        $sessions = collect();
        foreach ($serviceQuery->get() as $service) {
            $sessions->push($this->sessionPayload(
                $service,
                AttendanceSourceType::ChurchService->value,
            ));
        }
        foreach ($eventQuery->get() as $event) {
            $sessions->push($this->sessionPayload(
                $event,
                AttendanceSourceType::SpecialEvent->value,
            ));
        }

        $sorted = $sessions->sortBy([
            ['date', 'asc'],
            ['start_time', 'asc'],
        ])->values()->all();

        return ApiResponse::success($sorted);
    }

    public function roster(Request $request): JsonResponse
    {
        $this->authorize('create', AttendanceRecord::class);

        $sourceType = trim($request->string('source_type')->toString());
        $sourceId = $request->integer('source_id');
        if ($sourceType === '' || $sourceId < 1) {
            return ApiResponse::error('Select a service first.', 422);
        }

        $church = $this->church();
        $source = $this->attendanceService->resolveSource($church, $sourceType, $sourceId);
        abort_unless(
            $this->branchAccessService->canAccessBranchId($this->pastor(), $source->branch_id),
            403,
        );

        $source->syncLiveStatus();
        $mode = $this->attendanceService->attendanceMode($source);
        $summary = $this->attendanceService->summary($church, $sourceType, $sourceId);
        if ((int) $summary['total_count'] > 0) {
            return ApiResponse::error('Attendance already recorded for this service.', 422);
        }
        $presentMembers = $summary['records']->pluck('member_id')->filter()->map(fn ($id) => (int) $id)->all();
        $presentChildren = $summary['records']->pluck('dependant_id')->filter()->map(fn ($id) => (int) $id)->all();
        $graduationAge = $this->churchSettingsService->childGraduationAge($church);

        $members = [];
        $dependants = [];

        if ($mode !== 'sunday_school') {
            $membersQuery = Member::forChurch($church->id)
                ->activeMembers()
                ->orderBy('full_name');
            if ($source->branch_id) {
                $membersQuery->where('branch_id', $source->branch_id);
            }
            $members = $membersQuery
                ->get(['id', 'full_name', 'member_number'])
                ->map(fn (Member $member) => [
                    'id' => $member->id,
                    'full_name' => $member->full_name,
                    'member_number' => $member->member_number,
                    'present' => in_array($member->id, $presentMembers, true),
                ])
                ->values()
                ->all();
        }

        $childrenQuery = MemberDependant::forChurch($church->id)->orderBy('full_name');
        if ($mode === 'sunday_school') {
            $childrenQuery->forSundaySchool();
        } elseif ($mode === 'main_service') {
            $childrenQuery->forMainServiceAttendance($graduationAge);
        } else {
            $childrenQuery->children()->activeChildren($graduationAge);
        }

        $dependants = $childrenQuery
            ->get(['id', 'full_name', 'date_of_birth', 'gender'])
            ->map(fn (MemberDependant $child) => [
                'id' => $child->id,
                'full_name' => $child->full_name,
                'age' => $child->age(),
                'present' => in_array($child->id, $presentChildren, true),
            ])
            ->values()
            ->all();

        return ApiResponse::success([
            'session' => $this->sessionPayload($source, $sourceType),
            'mode' => $mode,
            'guests_count' => $summary['guests_count'],
            'notes' => $summary['records']->first()?->notes ?? '',
            'members' => $members,
            'dependants' => $dependants,
        ]);
    }

    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $user = $this->pastor();
        $church = $this->church();
        $sourceType = $request->validated('source_type');
        $sourceId = (int) $request->validated('source_id');
        $source = $this->attendanceService->resolveSource($church, $sourceType, $sourceId);

        abort_unless(
            $this->branchAccessService->canAccessBranchId($user, $source->branch_id),
            403,
        );

        $existing = $this->attendanceService->summary($church, $sourceType, $sourceId);
        if ((int) $existing['total_count'] > 0) {
            return ApiResponse::error('Attendance already recorded for this service.', 422);
        }

        $result = $this->attendanceService->sync(
            $church,
            $sourceType,
            $sourceId,
            $request->input('member_ids', []),
            $request->input('dependant_ids', []),
            (int) $request->input('guests_count', 0),
            $request->validated('notes'),
            $user,
        );

        return ApiResponse::success($result, 'Attendance saved.');
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<\App\Models\AttendanceRecord>  $query */
    private function periodTotals($query, $from, $to): array
    {
        $scoped = (clone $query)->whereBetween('attended_at', [$from, $to]);
        $members = (clone $scoped)->membersOnly()->count();
        $children = (clone $scoped)->childrenOnly()->count();
        $sessions = (clone $scoped)
            ->select('source_type', 'source_id')
            ->groupBy('source_type', 'source_id')
            ->get()
            ->count();

        return [
            'sessions' => $sessions,
            'members' => $members,
            'children' => $children,
            'total' => $members + $children,
            'average' => $sessions > 0 ? round(($members + $children) / $sessions, 1) : 0,
        ];
    }

    /** @return list<array<string, mixed>> */
    private function recentSessions(Request $request): array
    {
        $user = $this->pastor();
        $query = ChurchService::forChurch($user->church_id)
            ->where('status', '!=', ChurchServiceStatus::Cancelled->value)
            ->whereDate('service_date', '<=', now()->toDateString())
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->limit(20);
        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        $recent = [];
        foreach ($query->get() as $service) {
            $row = $this->sessionPayload($service, AttendanceSourceType::ChurchService->value);
            if ((int) $row['total_count'] < 1) {
                continue;
            }
            $recent[] = $row;
            if (count($recent) >= 8) {
                break;
            }
        }

        return $recent;
    }

    /** @return array<string, mixed> */
    private function sessionPayload(ChurchService|SpecialEvent $source, string $sourceType): array
    {
        $source->syncLiveStatus();
        $canRecord = $source->canRecordAttendance();
        $status = $source->status?->value;
        $summary = $this->attendanceService->summary(
            $this->church(),
            $sourceType,
            $source->id,
        );
        $alreadyRecorded = (int) $summary['total_count'] > 0;
        $state = match (true) {
            $status === 'cancelled' => 'cancelled',
            $alreadyRecorded => 'recorded',
            ! $canRecord => 'upcoming',
            $status === 'completed' => 'completed',
            default => 'ongoing',
        };

        $date = $source instanceof ChurchService
            ? $source->service_date?->toDateString()
            : $source->event_date?->toDateString();

        return [
            'source_type' => $sourceType,
            'source_id' => $source->id,
            'title' => $source instanceof ChurchService ? $source->displayTitle() : $source->title,
            'date' => $date,
            'start_time' => $this->clock($source->start_time),
            'end_time' => $this->clock($source->end_time),
            'venue' => $source->venue,
            'status' => $status,
            'attendance_state' => $state,
            'can_record' => $canRecord && ! $alreadyRecorded && $state !== 'upcoming' && $state !== 'cancelled' && $state !== 'recorded',
            'opens_at' => $source->attendanceOpensAt()?->toIso8601String(),
            'members_count' => $summary['members_count'],
            'children_count' => $summary['children_count'],
            'guests_count' => $summary['guests_count'],
            'total_count' => $summary['total_count'],
            'branch' => $source->relationLoaded('branch') && $source->branch ? [
                'id' => $source->branch->id,
                'name' => $source->branch->name,
            ] : null,
        ];
    }

    private function clock(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clock = substr((string) $value, 0, 5);

        return preg_match('/^\d{2}:\d{2}$/', $clock) ? $clock : null;
    }
}
