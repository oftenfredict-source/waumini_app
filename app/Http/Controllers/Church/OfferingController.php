<?php

namespace App\Http\Controllers\Church;

use App\Enums\FinancePaymentMethod;
use App\Enums\FinancialApprovalStatus;
use App\Enums\OfferingContributionType;
use App\Enums\OfferingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreOfferingRequest;
use App\Http\Requests\Church\UpdateOfferingRequest;
use App\Models\ChurchService;
use App\Models\Member;
use App\Models\Offering;
use App\Services\Church\OfferingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferingController extends Controller
{
    public function __construct(
        private readonly OfferingService $offeringService,
    ) {
        $this->authorizeResource(Offering::class, 'offering');
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;

        $query = Offering::forChurch($church->id)
            ->with(['member', 'churchService', 'recorder', 'approver'])
            ->latest('offering_date')
            ->latest('id');

        if ($memberId = $request->integer('member_id')) {
            $query->where('member_id', $memberId);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('approval_status', $status);
        }

        if ($type = $request->string('offering_type')->trim()->toString()) {
            $query->where('offering_type', $type);
        }

        if ($from = $request->string('from')->trim()->toString()) {
            $query->whereDate('offering_date', '>=', $from);
        }

        if ($to = $request->string('to')->trim()->toString()) {
            $query->whereDate('offering_date', '<=', $to);
        }

        if ($method = $request->string('payment_method')->trim()->toString()) {
            $query->where('payment_method', $method);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('member', function ($memberQuery) use ($search) {
                    $memberQuery->where('full_name', 'like', "%{$search}%")
                        ->orWhere('envelope_number', 'like', "%{$search}%");
                })->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('offering_type_other', 'like', "%{$search}%");
            });
        }

        $offerings = $query->paginate(20)->withQueryString();

        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        $statsQuery = Offering::forChurch($church->id);

        return view('church.offerings.index', [
            'offerings' => $offerings,
            'members' => $members,
            'paymentMethods' => FinancePaymentMethod::cases(),
            'offeringTypes' => OfferingType::cases(),
            'statuses' => FinancialApprovalStatus::cases(),
            'filters' => $request->only(['search', 'member_id', 'status', 'offering_type', 'from', 'to', 'payment_method']),
            'stats' => [
                'total_approved' => (float) (clone $statsQuery)->approved()->sum('amount'),
                'month_approved' => (float) (clone $statsQuery)->approved()
                    ->whereMonth('offering_date', now()->month)
                    ->whereYear('offering_date', now()->year)
                    ->sum('amount'),
                'pending_count' => (clone $statsQuery)->pendingApproval()->count(),
                'pending_amount' => (float) (clone $statsQuery)->pendingApproval()->sum('amount'),
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

        return view('church.offerings.create', [
            'members' => $members,
            'services' => $this->selectableServices($church->id),
            'paymentMethods' => FinancePaymentMethod::cases(),
            'offeringTypes' => OfferingType::cases(),
            'contributionTypes' => OfferingContributionType::cases(),
        ]);
    }

    public function store(StoreOfferingRequest $request): RedirectResponse
    {
        $church = $request->user()->church;
        $offering = $this->offeringService->create(
            $church,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('church.offerings.show', $offering)
            ->with('success', 'Offering recorded successfully and sent for approval.');
    }

    public function show(Offering $offering): View
    {
        $offering->load(['member', 'churchService', 'recorder', 'approver']);

        return view('church.offerings.show', ['offering' => $offering]);
    }

    public function edit(Offering $offering): View
    {
        $church = auth()->user()->church;
        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        return view('church.offerings.edit', [
            'offering' => $offering,
            'members' => $members,
            'services' => $this->selectableServices($church->id),
            'paymentMethods' => FinancePaymentMethod::cases(),
            'offeringTypes' => OfferingType::cases(),
            'contributionTypes' => OfferingContributionType::cases(),
        ]);
    }

    public function update(UpdateOfferingRequest $request, Offering $offering): RedirectResponse
    {
        $this->offeringService->update($offering, $request->validated());

        return redirect()
            ->route('church.offerings.show', $offering)
            ->with('success', 'Offering updated successfully.');
    }

    public function destroy(Offering $offering): RedirectResponse
    {
        $this->offeringService->delete($offering);

        return redirect()
            ->route('church.offerings.index')
            ->with('success', 'Offering deleted successfully.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ChurchService>
     */
    private function selectableServices(int $churchId)
    {
        return ChurchService::query()
            ->forOfferingSelection($churchId)
            ->get(['id', 'service_type', 'title', 'service_date', 'start_time']);
    }
}
