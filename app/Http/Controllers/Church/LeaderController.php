<?php

namespace App\Http\Controllers\Church;

use App\Enums\LeadershipPosition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreLeaderRequest;
use App\Models\Leader;
use App\Models\Member;
use App\Services\Church\LeaderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaderController extends Controller
{
    public function __construct(
        private readonly LeaderService $leaderService,
    ) {
        $this->authorizeResource(Leader::class, 'leader');
    }

    public function index(Request $request): View
    {
        $church = auth()->user()->church;

        $query = Leader::forChurch($church->id)
            ->with('member')
            ->latest('appointment_date');

        if ($position = $request->string('position')->trim()->toString()) {
            $query->where('position', $position);
        }

        if ($request->string('status')->toString() === 'active') {
            $query->active();
        } elseif ($request->string('status')->toString() === 'inactive') {
            $query->where('is_active', false);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%");
            });
        }

        $leaders = $query->paginate(15)->withQueryString();
        $positions = LeadershipPosition::options();

        return view('church.leadership.index', [
            'leaders' => $leaders,
            'positions' => $positions,
            'filters' => $request->only(['search', 'position', 'status']),
        ]);
    }

    public function create(): View
    {
        $church = auth()->user()->church;

        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_number']);

        return view('church.leadership.create', [
            'members' => $members,
            'positions' => LeadershipPosition::options(),
        ]);
    }

    public function store(StoreLeaderRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;
        $leader = $this->leaderService->assign($church, $request->validated());

        return redirect()
            ->route('church.leadership.show', $leader)
            ->with('success', 'Leadership position assigned successfully.');
    }

    public function show(Leader $leader): View
    {
        $leader->load('member');

        return view('church.leadership.show', compact('leader'));
    }

    public function deactivate(Leader $leader): RedirectResponse
    {
        $this->authorize('deactivate', $leader);

        $this->leaderService->deactivate($leader);

        return redirect()
            ->route('church.leadership.index')
            ->with('success', 'Leadership assignment ended successfully.');
    }
}
