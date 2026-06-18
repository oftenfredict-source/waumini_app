<?php

namespace App\Http\Controllers\Church;

use App\Enums\BereavementContributionType;
use App\Enums\BereavementPaymentMethod;
use App\Enums\BereavementStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\CloseBereavementRequest;
use App\Http\Requests\Church\RecordBereavementContributionRequest;
use App\Http\Requests\Church\StoreBereavementRequest;
use App\Http\Requests\Church\UpdateBereavementRequest;
use App\Models\BereavementEvent;
use App\Models\Member;
use App\Services\Church\BereavementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BereavementController extends Controller
{
    public function __construct(
        private readonly BereavementService $bereavementService,
    ) {
        $this->authorizeResource(BereavementEvent::class, 'bereavement');
    }

    public function index(Request $request): View
    {
        $church = auth()->user()->church;

        $query = BereavementEvent::forChurch($church->id)
            ->with(['creator', 'affectedMember'])
            ->withSum(['contributions as total_raised' => fn ($q) => $q->where('has_contributed', true)], 'amount')
            ->withCount(['contributions as contributors_count' => fn ($q) => $q->where('has_contributed', true)])
            ->latest('incident_date');

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($from = $request->string('from')->trim()->toString()) {
            $query->whereDate('incident_date', '>=', $from);
        }

        if ($to = $request->string('to')->trim()->toString()) {
            $query->whereDate('incident_date', '<=', $to);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('deceased_name', 'like', "%{$search}%")
                    ->orWhere('family_details', 'like', "%{$search}%")
                    ->orWhere('related_departments', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate(15)->withQueryString();

        return view('church.bereavements.index', [
            'events' => $events,
            'statuses' => BereavementStatus::cases(),
            'filters' => $request->only(['search', 'status', 'from', 'to']),
        ]);
    }

    public function create(): View
    {
        $church = auth()->user()->church;
        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        return view('church.bereavements.create', [
            'members' => $members,
        ]);
    }

    public function store(StoreBereavementRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;
        $event = $this->bereavementService->create(
            $church,
            $request->validated(),
            auth()->user()
        );

        return redirect()
            ->route('church.bereavements.show', $event)
            ->with('success', 'Bereavement event created successfully.');
    }

    public function show(BereavementEvent $bereavement): View
    {
        $bereavement->load([
            'contributions.member',
            'contributions.recorder',
            'creator',
            'affectedMember',
        ]);

        $contributors = $bereavement->contributions()
            ->where('has_contributed', true)
            ->with('member')
            ->orderByDesc('contribution_date')
            ->get();

        $pending = $bereavement->contributions()
            ->where('has_contributed', false)
            ->with('member')
            ->get()
            ->sortBy(fn ($c) => $c->member?->full_name);

        $contributedMemberIds = $contributors->pluck('member_id')->all();

        $availableMembers = Member::forChurch($bereavement->church_id)
            ->where('status', 'active')
            ->whereNotIn('id', $contributedMemberIds)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        return view('church.bereavements.show', [
            'event' => $bereavement,
            'totalContributions' => $bereavement->totalContributions(),
            'contributorsCount' => $bereavement->contributorsCount(),
            'pendingCount' => $bereavement->pendingCount(),
            'daysRemaining' => $bereavement->daysRemaining(),
            'contributors' => $contributors,
            'pending' => $pending,
            'availableMembers' => $availableMembers,
            'contributionTypes' => BereavementContributionType::cases(),
            'paymentMethods' => BereavementPaymentMethod::cases(),
        ]);
    }

    public function edit(BereavementEvent $bereavement): View
    {
        $church = auth()->user()->church;
        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        return view('church.bereavements.edit', [
            'event' => $bereavement,
            'members' => $members,
        ]);
    }

    public function update(UpdateBereavementRequest $request, BereavementEvent $bereavement): RedirectResponse
    {
        $this->bereavementService->update($bereavement, $request->validated());

        return redirect()
            ->route('church.bereavements.show', $bereavement)
            ->with('success', 'Bereavement event updated successfully.');
    }

    public function destroy(BereavementEvent $bereavement): RedirectResponse
    {
        $this->bereavementService->delete($bereavement);

        return redirect()
            ->route('church.bereavements.index')
            ->with('success', 'Bereavement event deleted successfully.');
    }

    public function recordContribution(
        RecordBereavementContributionRequest $request,
        BereavementEvent $bereavement
    ): RedirectResponse {
        $this->bereavementService->recordContribution(
            $bereavement,
            $request->validated(),
            auth()->user()
        );

        return redirect()
            ->route('church.bereavements.show', $bereavement)
            ->with('success', 'Contribution recorded successfully.');
    }

    public function markNonContributor(Request $request, BereavementEvent $bereavement): RedirectResponse
    {
        $this->authorize('manageContributions', $bereavement);

        $churchId = auth()->user()->church_id;
        $validated = $request->validate([
            'member_id' => [
                'required',
                \Illuminate\Validation\Rule::exists('members', 'id')
                    ->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
        ]);

        $this->bereavementService->markNonContributor(
            $bereavement,
            (int) $validated['member_id'],
            auth()->user()
        );

        return redirect()
            ->route('church.bereavements.show', $bereavement)
            ->with('success', 'Member marked as pending.');
    }

    public function close(CloseBereavementRequest $request, BereavementEvent $bereavement): RedirectResponse
    {
        $this->bereavementService->close(
            $bereavement,
            $request->validated('fund_usage')
        );

        return redirect()
            ->route('church.bereavements.show', $bereavement)
            ->with('success', 'Bereavement event closed successfully.');
    }
}
