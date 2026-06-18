<?php

namespace App\Http\Controllers\Church;

use App\Enums\FinancePaymentMethod;
use App\Enums\PledgePaymentFrequency;
use App\Enums\PledgeStatus;
use App\Enums\PledgeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\RecordPledgePaymentRequest;
use App\Http\Requests\Church\StorePledgeRequest;
use App\Http\Requests\Church\UpdatePledgeRequest;
use App\Models\Member;
use App\Models\Pledge;
use App\Services\Church\PledgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PledgeController extends Controller
{
    public function __construct(
        private readonly PledgeService $pledgeService,
    ) {
        $this->authorizeResource(Pledge::class, 'pledge');
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;

        $query = Pledge::forChurch($church->id)
            ->with(['member', 'recorder'])
            ->latest('pledge_date')
            ->latest('id');

        if ($memberId = $request->integer('member_id')) {
            $query->where('member_id', $memberId);
        }

        if ($type = $request->string('pledge_type')->trim()->toString()) {
            $query->where('pledge_type', $type);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('member', function ($memberQuery) use ($search) {
                    $memberQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('envelope_number', 'like', "%{$search}%");
                })->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('pledge_type_other', 'like', "%{$search}%");
            });
        }

        $pledges = $query->paginate(20)->withQueryString();

        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        $statsQuery = Pledge::forChurch($church->id);

        return view('church.pledges.index', [
            'pledges' => $pledges,
            'members' => $members,
            'pledgeTypes' => PledgeType::cases(),
            'statuses' => PledgeStatus::cases(),
            'filters' => $request->only(['search', 'member_id', 'pledge_type', 'status']),
            'stats' => [
                'active_count' => (clone $statsQuery)->where('status', PledgeStatus::Active)->count(),
                'total_pledged' => (float) (clone $statsQuery)->sum('pledge_amount'),
                'total_paid' => (float) (clone $statsQuery)->sum('amount_paid'),
                'completed_count' => (clone $statsQuery)->where('status', PledgeStatus::Completed)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $church = auth()->user()->church;
        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        return view('church.pledges.create', [
            'members' => $members,
            'pledgeTypes' => PledgeType::cases(),
            'frequencies' => PledgePaymentFrequency::cases(),
        ]);
    }

    public function store(StorePledgeRequest $request): RedirectResponse
    {
        $pledge = $this->pledgeService->create(
            $request->user()->church,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('church.pledges.show', $pledge)
            ->with('success', 'Pledge recorded successfully.');
    }

    public function show(Pledge $pledge): View
    {
        $pledge->load([
            'member',
            'recorder',
            'payments' => fn ($q) => $q->with(['recorder', 'approver'])->latest('payment_date'),
        ]);

        return view('church.pledges.show', [
            'pledge' => $pledge,
            'paymentMethods' => FinancePaymentMethod::cases(),
        ]);
    }

    public function edit(Pledge $pledge): View
    {
        $church = auth()->user()->church;
        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        return view('church.pledges.edit', [
            'pledge' => $pledge,
            'members' => $members,
            'pledgeTypes' => PledgeType::cases(),
            'frequencies' => PledgePaymentFrequency::cases(),
        ]);
    }

    public function update(UpdatePledgeRequest $request, Pledge $pledge): RedirectResponse
    {
        $this->pledgeService->update($pledge, $request->validated());

        return redirect()
            ->route('church.pledges.show', $pledge)
            ->with('success', 'Pledge updated successfully.');
    }

    public function destroy(Pledge $pledge): RedirectResponse
    {
        $this->pledgeService->delete($pledge);

        return redirect()
            ->route('church.pledges.index')
            ->with('success', 'Pledge deleted successfully.');
    }

    public function recordPayment(RecordPledgePaymentRequest $request, Pledge $pledge): RedirectResponse
    {
        $this->pledgeService->recordPayment(
            $pledge,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('church.pledges.show', $pledge)
            ->with('success', 'Pledge payment recorded and sent for approval.');
    }
}
