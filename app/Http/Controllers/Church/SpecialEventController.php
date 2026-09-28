<?php

namespace App\Http\Controllers\Church;

use App\Enums\SpecialEventCategory;
use App\Enums\SpecialEventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreSpecialEventRequest;
use App\Http\Requests\Church\UpdateSpecialEventRequest;
use App\Models\SpecialEvent;
use App\Services\Church\BranchAccessService;
use App\Services\Church\SpecialEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecialEventController extends Controller
{
    public function __construct(
        private readonly SpecialEventService $specialEventService,
        private readonly BranchAccessService $branchAccessService,
    ) {
        $this->authorizeResource(SpecialEvent::class, 'special_event');
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $church = $user->church;

        $query = SpecialEvent::forChurch($church->id)
            ->with(['creator', 'branch'])
            ->latest('event_date')
            ->latest('start_time');

        $this->branchAccessService->applyBranchScope($query, $user);

        if ($category = $request->string('category')->trim()->toString()) {
            $query->where('category', $category);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($from = $request->string('from')->trim()->toString()) {
            $query->whereDate('event_date', '>=', $from);
        }

        if ($to = $request->string('to')->trim()->toString()) {
            $query->whereDate('event_date', '<=', $to);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('speaker', 'like', "%{$search}%")
                    ->orWhere('venue', 'like', "%{$search}%")
                    ->orWhere('category_other', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate(15)->withQueryString();
        $events->getCollection()->each->syncLiveStatus();

        return view('church.special-events.index', [
            'events' => $events,
            'categories' => SpecialEventCategory::cases(),
            'statuses' => SpecialEventStatus::cases(),
            'filters' => $request->only(['search', 'category', 'status', 'from', 'to']),
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();

        return view('church.special-events.create', [
            'categories' => SpecialEventCategory::cases(),
            'statuses' => SpecialEventStatus::cases(),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'defaultBranchId' => $this->branchAccessService->effectiveBranchId($user),
            'canSelectBranch' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user)
                && ! $this->branchAccessService->sessionBranchId($user),
            'branchesEnabled' => $this->branchAccessService->branchesFeatureEnabled($user),
        ]);
    }

    public function store(StoreSpecialEventRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;
        $event = $this->specialEventService->create(
            $church,
            $request->validated(),
            auth()->user()
        );

        return redirect()
            ->route('church.special-events.show', $event)
            ->with('success', 'Special event created successfully.');
    }

    public function show(SpecialEvent $specialEvent): View
    {
        $specialEvent->load('creator');
        $specialEvent->syncLiveStatus();

        return view('church.special-events.show', ['event' => $specialEvent]);
    }

    public function edit(SpecialEvent $specialEvent): View
    {
        $user = auth()->user();

        return view('church.special-events.edit', [
            'event' => $specialEvent,
            'categories' => SpecialEventCategory::cases(),
            'statuses' => SpecialEventStatus::cases(),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'defaultBranchId' => $specialEvent->branch_id,
            'canSelectBranch' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user)
                && ! $this->branchAccessService->sessionBranchId($user),
            'branchesEnabled' => $this->branchAccessService->branchesFeatureEnabled($user),
        ]);
    }

    public function update(UpdateSpecialEventRequest $request, SpecialEvent $specialEvent): RedirectResponse
    {
        $this->specialEventService->update($specialEvent, $request->validated(), auth()->user());

        return redirect()
            ->route('church.special-events.show', $specialEvent)
            ->with('success', 'Special event updated successfully.');
    }

    public function destroy(SpecialEvent $specialEvent): RedirectResponse
    {
        $this->specialEventService->delete($specialEvent);

        return redirect()
            ->route('church.special-events.index')
            ->with('success', 'Special event deleted successfully.');
    }
}
