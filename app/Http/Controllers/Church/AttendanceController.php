<?php

namespace App\Http\Controllers\Church;

use App\Enums\AttendanceSourceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreAttendanceRequest;
use App\Models\AttendanceRecord;
use App\Models\ChurchService;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Models\SpecialEvent;
use App\Services\Church\AttendanceService;
use App\Services\Church\BranchAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly BranchAccessService $branchAccessService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $user = auth()->user();
        $church = $user->church;

        $serviceQuery = ChurchService::forChurch($church->id)->latest('service_date');
        $eventQuery = SpecialEvent::forChurch($church->id)->latest('event_date');
        $this->branchAccessService->applyBranchScope($serviceQuery, $user);
        $this->branchAccessService->applyBranchScope($eventQuery, $user);

        if ($search = $request->string('search')->trim()->toString()) {
            $serviceQuery->where(function ($q) use ($search) {
                $q->where('theme', 'like', "%{$search}%")
                    ->orWhere('preacher', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
            $eventQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('speaker', 'like', "%{$search}%");
            });
        }

        $services = $serviceQuery->limit(50)->get()->map(function (ChurchService $service) use ($church) {
            $summary = $this->attendanceService->summary(
                $church,
                AttendanceSourceType::ChurchService->value,
                $service->id
            );

            return [
                'source_type' => AttendanceSourceType::ChurchService->value,
                'source_id' => $service->id,
                'label' => $this->attendanceService->sourceLabel($service),
                'date' => $service->service_date,
                'type_label' => $service->displayTitle(),
                'members_count' => $summary['members_count'],
                'children_count' => $summary['children_count'],
                'guests_count' => $summary['guests_count'],
                'total_count' => $summary['total_count'],
                'has_attendance' => $summary['total_count'] > 0,
                'can_record' => $service->canRecordAttendance(),
                'opens_at' => $service->attendanceOpensAt(),
            ];
        });

        $events = $eventQuery->limit(50)->get()->map(function (SpecialEvent $event) use ($church) {
            $summary = $this->attendanceService->summary(
                $church,
                AttendanceSourceType::SpecialEvent->value,
                $event->id
            );

            return [
                'source_type' => AttendanceSourceType::SpecialEvent->value,
                'source_id' => $event->id,
                'label' => $this->attendanceService->sourceLabel($event),
                'date' => $event->event_date,
                'type_label' => $event->title,
                'members_count' => $summary['members_count'],
                'children_count' => $summary['children_count'],
                'guests_count' => $summary['guests_count'],
                'total_count' => $summary['total_count'],
                'has_attendance' => $summary['total_count'] > 0,
                'can_record' => $event->canRecordAttendance(),
                'opens_at' => $event->attendanceOpensAt(),
            ];
        });

        $sessions = $services->concat($events)->sortByDesc('date')->values();

        $membersMarked = AttendanceRecord::forChurch($church->id)->membersOnly();
        $childrenMarked = AttendanceRecord::forChurch($church->id)->childrenOnly();
        $this->branchAccessService->applyBranchScope($membersMarked, $user);
        $this->branchAccessService->applyBranchScope($childrenMarked, $user);

        $stats = [
            'recorded_sessions' => $sessions->where('has_attendance', true)->count(),
            'total_members_marked' => $membersMarked->count(),
            'total_children_marked' => $childrenMarked->count(),
        ];

        return view('church.attendance.index', [
            'sessions' => $sessions,
            'stats' => $stats,
            'filters' => $request->only(['search']),
        ]);
    }

    public function statistics(Request $request): View
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $church = auth()->user()->church;
        $end = $request->filled('end_date')
            ? \Illuminate\Support\Carbon::parse($request->string('end_date')->toString())->endOfDay()
            : now()->endOfDay();
        $start = $request->filled('start_date')
            ? \Illuminate\Support\Carbon::parse($request->string('start_date')->toString())->startOfDay()
            : $end->copy()->subMonths(3)->startOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $report = $this->attendanceService->statistics($church, $start, $end);

        return view('church.attendance.statistics', [
            'report' => $report,
            'filters' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AttendanceRecord::class);

        $user = auth()->user();
        $church = $user->church;
        $sourceType = $request->string('source_type')->toString();
        $sourceId = $request->integer('source_id');

        $churchServicesQuery = ChurchService::forChurch($church->id)->orderByDesc('service_date');
        $this->branchAccessService->applyBranchScope($churchServicesQuery, $user);
        $churchServices = $churchServicesQuery->get();

        $memberServices = $churchServices->filter(fn (ChurchService $service) => ! $service->isSundaySchool());
        $sundaySchoolServices = $churchServices->filter(fn (ChurchService $service) => $service->isSundaySchool());

        $specialEventsQuery = SpecialEvent::forChurch($church->id)->orderByDesc('event_date');
        $this->branchAccessService->applyBranchScope($specialEventsQuery, $user);
        $specialEvents = $specialEventsQuery->get();

        $selectedSource = null;
        $attendedMemberIds = [];
        $attendedDependantIds = [];
        $guestsCount = 0;
        $notes = '';
        $canRecordAttendance = false;
        $attendanceOpensAt = null;

        $attendanceMode = null;

        if ($sourceType && $sourceId) {
            $selectedSource = $this->attendanceService->resolveSource($church, $sourceType, $sourceId);
            abort_unless(
                $this->branchAccessService->canAccessBranchId($user, $selectedSource->branch_id),
                403
            );
            $attendanceMode = $this->attendanceService->attendanceMode($selectedSource);
            $canRecordAttendance = $selectedSource->canRecordAttendance();
            $attendanceOpensAt = $selectedSource->attendanceOpensAt();
            $summary = $this->attendanceService->summary($church, $sourceType, $sourceId);
            $attendedMemberIds = $summary['records']->pluck('member_id')->filter()->all();
            $attendedDependantIds = $summary['records']->pluck('dependant_id')->filter()->all();
            $guestsCount = $summary['guests_count'];
            $notes = $summary['records']->first()?->notes ?? '';
        }

        $membersQuery = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderByRaw('CASE WHEN envelope_number IS NULL OR envelope_number = "" THEN 1 ELSE 0 END')
            ->orderBy('envelope_number')
            ->orderBy('full_name');
        $this->branchAccessService->applyBranchScope($membersQuery, $user);
        $members = $membersQuery->get(['id', 'full_name', 'member_number', 'envelope_number']);

        $graduationAge = app(\App\Services\Church\ChurchSettingsService::class)->childGraduationAge($church);

        $sundaySchoolChildren = MemberDependant::forChurch($church->id)
            ->forSundaySchool()
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'gender', 'date_of_birth', 'member_id']);

        $teenagers = MemberDependant::forChurch($church->id)
            ->forMainServiceAttendance($graduationAge)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'gender', 'date_of_birth', 'member_id']);

        $allChildren = MemberDependant::forChurch($church->id)
            ->children()
            ->activeChildren($graduationAge)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'gender', 'date_of_birth', 'member_id']);

        return view('church.attendance.create', [
            'sourceTypes' => AttendanceSourceType::cases(),
            'memberServices' => $memberServices,
            'sundaySchoolServices' => $sundaySchoolServices,
            'specialEvents' => $specialEvents,
            'selectedSourceType' => $sourceType,
            'selectedSourceId' => $sourceId,
            'selectedSource' => $selectedSource,
            'attendanceMode' => $attendanceMode,
            'members' => $members,
            'sundaySchoolChildren' => $sundaySchoolChildren,
            'teenagers' => $teenagers,
            'allChildren' => $allChildren,
            'attendedMemberIds' => $attendedMemberIds,
            'attendedDependantIds' => $attendedDependantIds,
            'guestsCount' => $guestsCount,
            'notes' => $notes,
            'canRecordAttendance' => $canRecordAttendance,
            'attendanceOpensAt' => $attendanceOpensAt,
        ]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $church = $user->church;
        $sourceType = $request->validated('source_type');
        $sourceId = (int) $request->validated('source_id');

        $source = $this->attendanceService->resolveSource($church, $sourceType, $sourceId);
        abort_unless(
            $this->branchAccessService->canAccessBranchId($user, $source->branch_id),
            403
        );

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

        return redirect()
            ->route('church.attendance.show', [
                'source_type' => $sourceType,
                'source_id' => $sourceId,
            ])
            ->with('success', "Attendance saved: {$result['total_count']} total ({$result['members_count']} members, {$result['children_count']} children, {$result['guests_count']} guests).");
    }

    public function show(Request $request): View
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $user = auth()->user();
        $church = $user->church;
        $sourceType = $request->string('source_type')->toString();
        $sourceId = $request->integer('source_id');

        abort_unless($sourceType && $sourceId, 404);

        $summary = $this->attendanceService->summary($church, $sourceType, $sourceId);
        abort_unless(
            $this->branchAccessService->canAccessBranchId($user, $summary['source']->branch_id),
            403
        );

        return view('church.attendance.show', [
            'sourceType' => AttendanceSourceType::from($sourceType),
            'sourceId' => $sourceId,
            'summary' => $summary,
            'sourceLabel' => $this->attendanceService->sourceLabel($summary['source']),
            'attendanceMode' => $this->attendanceService->attendanceMode($summary['source']),
            'canRecordAttendance' => $summary['source']->canRecordAttendance(),
            'attendanceOpensAt' => $summary['source']->attendanceOpensAt(),
        ]);
    }
}
