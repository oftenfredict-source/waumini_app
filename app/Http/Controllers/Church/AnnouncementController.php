<?php

namespace App\Http\Controllers\Church;

use App\Enums\AnnouncementTargetType;
use App\Enums\AnnouncementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\Department;
use App\Models\Member;
use App\Services\Church\AnnouncementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function __construct(
        private readonly AnnouncementService $announcementService,
    ) {
        $this->authorizeResource(Announcement::class, 'announcement');
    }

    public function index(Request $request): View
    {
        $church = auth()->user()->church;

        $query = Announcement::forChurch($church->id)
            ->with(['creator', 'department'])
            ->withCount('targetedMembers')
            ->latest('is_pinned')
            ->latest('created_at');

        if ($type = $request->string('type')->trim()->toString()) {
            $query->where('type', $type);
        }

        if ($request->string('status')->toString() === 'active') {
            $query->active();
        } elseif ($request->string('status')->toString() === 'inactive') {
            $query->where('is_active', false);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $announcements = $query->paginate(15)->withQueryString();

        return view('church.announcements.index', [
            'announcements' => $announcements,
            'types' => AnnouncementType::cases(),
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    public function create(): View
    {
        $church = auth()->user()->church;

        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_number']);

        $departments = Department::forChurch($church->id)
            ->where('status', 'active')
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return view('church.announcements.create', [
            'members' => $members,
            'departments' => $departments,
            'types' => AnnouncementType::cases(),
            'targetTypes' => AnnouncementTargetType::cases(),
        ]);
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;
        $memberIds = $request->input('member_ids', []);

        $announcement = $this->announcementService->create(
            $church,
            $request->safe()->except(['member_ids']),
            $memberIds
        );

        return redirect()
            ->route('church.announcements.show', $announcement)
            ->with('success', 'Announcement created successfully.');
    }

    public function show(Announcement $announcement): View
    {
        $announcement->load(['creator', 'department', 'targetedMembers']);

        return view('church.announcements.show', compact('announcement'));
    }
}
