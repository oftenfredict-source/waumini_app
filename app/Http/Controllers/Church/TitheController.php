<?php

namespace App\Http\Controllers\Church;

use App\Enums\FinancePaymentMethod;
use App\Enums\FinancialApprovalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreTitheRequest;
use App\Http\Requests\Church\UpdateTitheRequest;
use App\Models\Member;
use App\Models\Tithe;
use App\Services\Church\TitheService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TitheController extends Controller
{
    public function __construct(
        private readonly TitheService $titheService,
    ) {
        $this->authorizeResource(Tithe::class, 'tithe');
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;

        $query = Tithe::forChurch($church->id)
            ->with(['member', 'recorder', 'approver'])
            ->latest('tithe_date')
            ->latest('id');

        if ($memberId = $request->integer('member_id')) {
            $query->where('member_id', $memberId);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('approval_status', $status);
        }

        if ($from = $request->string('from')->trim()->toString()) {
            $query->whereDate('tithe_date', '>=', $from);
        }

        if ($to = $request->string('to')->trim()->toString()) {
            $query->whereDate('tithe_date', '<=', $to);
        }

        if ($method = $request->string('payment_method')->trim()->toString()) {
            $query->where('payment_method', $method);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('envelope_number', 'like', "%{$search}%");
            });
        }

        $tithes = $query->paginate(20)->withQueryString();

        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        $statsQuery = Tithe::forChurch($church->id);

        return view('church.tithes.index', [
            'tithes' => $tithes,
            'members' => $members,
            'paymentMethods' => FinancePaymentMethod::cases(),
            'statuses' => FinancialApprovalStatus::cases(),
            'filters' => $request->only(['search', 'member_id', 'status', 'from', 'to', 'payment_method']),
            'stats' => [
                'total_approved' => (float) (clone $statsQuery)->approved()->sum('amount'),
                'month_approved' => (float) (clone $statsQuery)->approved()
                    ->whereMonth('tithe_date', now()->month)
                    ->whereYear('tithe_date', now()->year)
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

        return view('church.tithes.create', [
            'members' => $members,
            'paymentMethods' => FinancePaymentMethod::cases(),
        ]);
    }

    public function store(StoreTitheRequest $request): RedirectResponse
    {
        $church = $request->user()->church;
        $tithe = $this->titheService->create(
            $church,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('church.tithes.show', $tithe)
            ->with('success', 'Tithe recorded successfully and sent for approval.');
    }

    public function show(Tithe $tithe): View
    {
        $tithe->load(['member', 'recorder', 'approver']);

        return view('church.tithes.show', ['tithe' => $tithe]);
    }

    public function edit(Tithe $tithe): View
    {
        $church = auth()->user()->church;
        $members = Member::forChurch($church->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number']);

        return view('church.tithes.edit', [
            'tithe' => $tithe,
            'members' => $members,
            'paymentMethods' => FinancePaymentMethod::cases(),
        ]);
    }

    public function update(UpdateTitheRequest $request, Tithe $tithe): RedirectResponse
    {
        $this->titheService->update($tithe, $request->validated());

        return redirect()
            ->route('church.tithes.show', $tithe)
            ->with('success', 'Tithe updated successfully.');
    }

    public function destroy(Tithe $tithe): RedirectResponse
    {
        $this->titheService->delete($tithe);

        return redirect()
            ->route('church.tithes.index')
            ->with('success', 'Tithe deleted successfully.');
    }
}
