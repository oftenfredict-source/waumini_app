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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $church = auth()->user()->church;

        $serviceQuery = ChurchService::forChurch($church->id)->latest('service_date');
        $eventQuery = SpecialEvent::forChurch($church->id)->latest('event_date');

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
            ];
        });

        $sessions = $services->concat($events)->sortByDesc('date')->values();

        $stats = [
            'recorded_sessions' => $sessions->where('has_attendance', true)->count(),
            'total_members_marked' => AttendanceRecord::forChurch($church->id)->membersOnly()->count(),
            'total_children_marked' => AttendanceRecord::forChurch($church->id)->childrenOnly()->count(),
        ];

        return view('church.attendance.index', [
            'sessions' => $sessions,
            'stats' => $stats,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AttendanceRecord::class);

        $church = auth()->user()->church;
        $sourceType = $request->string('source_type')->toString();
        $sourceId = $request->integer('source_id');

        $churchServices = ChurchService::forChurch($church->id)
            ->orderByDesc('service_date')
            ->get();

        $memberServices = $churchServices->filter(fn (ChurchService $service) => ! $service->isSundaySchool());
        $sundaySchoolServices = $churchServices->filter(fn (ChurchService $service) => $service->isSundaySchool());

        $specialEvents = SpecialEvent::forChurch($church->id)
            ->orderByDesc('event_date')
            ->get();

        $selectedSource = null;
        $attendedMemberIds = [];
        $attendedDependantIds = [];
        $guestsCount = 0;
        $notes = '';

        $attendanceMode = null;

        if ($sourceType && $sourceId) {
            $selectedSource = $this->attendanceService->resolveSource($church, $sourceType, $sourceId);
            $attendanceMode = $this->attendanceService->attendanceMode($selectedSource);
            $summary = $this->attendanceService->summary($church, $sourceType, $sourceId);
            $attendedMemberIds = $summary['records']->pluck('member_id')->filter()->all();
            $attendedDependantIds = $summary['records']->pluck('dependant_id')->filter()->all();
            $guestsCount = $summary['guests_count'];
            $notes = $summary['records']->first()?->notes ?? '';
        }

        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_number', 'envelope_number']);

        $sundaySchoolChildren = MemberDependant::forChurch($church->id)
            ->forSundaySchool()
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'gender', 'date_of_birth', 'member_id']);

        $teenagers = MemberDependant::forChurch($church->id)
            ->forMainServiceAttendance()
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'gender', 'date_of_birth', 'member_id']);

        $allChildren = MemberDependant::forChurch($church->id)
            ->children()
            ->whereNull('linked_member_id')
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
        ]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;

        $result = $this->attendanceService->sync(
            $church,
            $request->validated('source_type'),
            (int) $request->validated('source_id'),
            $request->input('member_ids', []),
            $request->input('dependant_ids', []),
            (int) $request->input('guests_count', 0),
            $request->validated('notes'),
            auth()->user(),
        );

        return redirect()
            ->route('church.attendance.show', [
                'source_type' => $request->validated('source_type'),
                'source_id' => $request->validated('source_id'),
            ])
            ->with('success', "Attendance saved: {$result['total_count']} total ({$result['members_count']} members, {$result['children_count']} children, {$result['guests_count']} guests).");
    }

    public function show(Request $request): View
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $church = auth()->user()->church;
        $sourceType = $request->string('source_type')->toString();
        $sourceId = $request->integer('source_id');

        abort_unless($sourceType && $sourceId, 404);

        $summary = $this->attendanceService->summary($church, $sourceType, $sourceId);

        return view('church.attendance.show', [
            'sourceType' => AttendanceSourceType::from($sourceType),
            'sourceId' => $sourceId,
            'summary' => $summary,
            'sourceLabel' => $this->attendanceService->sourceLabel($summary['source']),
            'attendanceMode' => $this->attendanceService->attendanceMode($summary['source']),
        ]);
    }
}
