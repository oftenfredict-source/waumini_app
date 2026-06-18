<?php

namespace App\Services\Church;

use App\Models\Offering;
use App\Models\Expense;
use App\Models\Pledge;
use App\Models\PledgePayment;
use App\Models\Tithe;
use App\Services\Church\FinanceApprovalService;
use App\Models\BereavementContribution;
use App\Models\BereavementEvent;
use App\Models\Church;
use App\Enums\PledgeStatus;
use App\Enums\ExpenseStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinanceDashboardService
{
    public function __construct(
        private readonly FinanceApprovalService $financeApprovalService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Church $church, ?int $year = null, ?int $month = null): array
    {
        $now = now();
        $year = $year ?? (int) $now->year;
        $month = $month ?? (int) $now->month;

        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $previousStart = $periodStart->copy()->subMonth()->startOfMonth();
        $previousEnd = $periodStart->copy()->subMonth()->endOfMonth();

        $bereavementMonth = $this->bereavementIncomeBetween($church->id, $periodStart, $periodEnd);
        $bereavementPrevious = $this->bereavementIncomeBetween($church->id, $previousStart, $previousEnd);
        $bereavementAllTime = $this->bereavementIncomeBetween($church->id);

        $tithes = $this->approvedIncomeBetween(Tithe::class, 'tithe_date', $church->id, $periodStart, $periodEnd);
        $offerings = $this->approvedIncomeBetween(Offering::class, 'offering_date', $church->id, $periodStart, $periodEnd);
        $pledges = $this->approvedIncomeBetween(PledgePayment::class, 'payment_date', $church->id, $periodStart, $periodEnd);
        $expensesMonth = $this->paidExpensesBetween($church->id, $periodStart, $periodEnd);
        $expensesYear = $this->paidExpensesBetween($church->id, $periodStart->copy()->startOfYear(), $periodStart->copy()->endOfYear());
        $expensesAllTime = $this->paidExpensesBetween($church->id);

        $totalIncome = $tithes + $offerings + $pledges + $bereavementMonth;
        $previousIncome = $bereavementPrevious
            + $this->approvedIncomeBetween(Tithe::class, 'tithe_date', $church->id, $previousStart, $previousEnd)
            + $this->approvedIncomeBetween(Offering::class, 'offering_date', $church->id, $previousStart, $previousEnd)
            + $this->approvedIncomeBetween(PledgePayment::class, 'payment_date', $church->id, $previousStart, $previousEnd);
        $allTimeIncome = $this->approvedIncomeBetween(Tithe::class, 'tithe_date', $church->id)
            + $this->approvedIncomeBetween(Offering::class, 'offering_date', $church->id)
            + $this->approvedIncomeBetween(PledgePayment::class, 'payment_date', $church->id)
            + $bereavementAllTime;
        $allTimeExpenses = $expensesAllTime;

        $incomeBreakdown = $this->buildIncomeBreakdown([
            'tithes' => $tithes,
            'offerings' => $offerings,
            'pledges' => $pledges,
            'bereavements' => $bereavementMonth,
        ], $totalIncome);

        $pendingApprovals = $this->financeApprovalService->pendingSummary($church->id);

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'label' => $periodStart->format('F Y'),
                'input' => $periodStart->format('Y-m'),
            ],
            'summary' => [
                'total_income' => $totalIncome,
                'total_expenses' => $expensesMonth,
                'expenses_year' => $expensesYear,
                'net_balance' => $totalIncome - $expensesMonth,
                'all_time_balance' => $allTimeIncome - $allTimeExpenses,
                'income_change_percent' => $this->percentChange($previousIncome, $totalIncome),
                'bereavement_month' => $bereavementMonth,
                'active_pledges' => Pledge::forChurch($church->id)
                    ->whereIn('status', [PledgeStatus::Active->value, PledgeStatus::Overdue->value])
                    ->count(),
                'pending_approvals_count' => $pendingApprovals['count'],
                'pending_approvals_amount' => $pendingApprovals['amount'],
            ],
            'income_breakdown' => $incomeBreakdown,
            'income_trend' => $this->incomeTrend($church->id, 6),
            'expense_trend' => $this->emptyTrend(6),
            'recent_transactions' => $this->recentTransactions($church->id, 8),
            'open_bereavements' => $this->openBereavements($church->id),
            'quick_stats' => [
                'contributors_this_month' => $this->contributorsThisMonth($church->id, $periodStart, $periodEnd),
                'open_collections' => $this->openBereavements($church->id)->count(),
                'average_contribution' => $this->averageBereavementContribution($church->id, $periodStart, $periodEnd),
            ],
        ];
    }

    private function approvedIncomeBetween(
        string $modelClass,
        string $dateField,
        int $churchId,
        ?Carbon $from = null,
        ?Carbon $to = null
    ): float {
        $query = $modelClass::forChurch($churchId)->approved();

        if ($from) {
            $query->whereDate($dateField, '>=', $from);
        }

        if ($to) {
            $query->whereDate($dateField, '<=', $to);
        }

        return (float) $query->sum('amount');
    }

    private function bereavementIncomeBetween(int $churchId, ?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = BereavementContribution::query()
            ->where('has_contributed', true)
            ->whereHas('bereavementEvent', fn ($q) => $q->where('church_id', $churchId));

        if ($from) {
            $query->whereDate('contribution_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('contribution_date', '<=', $to);
        }

        return (float) $query->sum('amount');
    }

    /**
     * @param  array<string, float>  $amounts
     * @return list<array<string, mixed>>
     */
    private function buildIncomeBreakdown(array $amounts, float $total): array
    {
        $meta = [
            'tithes' => ['label' => 'Tithes', 'color' => '#940000', 'icon' => 'fa-money'],
            'offerings' => ['label' => 'Offerings', 'color' => '#28a745', 'icon' => 'fa-gift'],
            'pledges' => ['label' => 'Pledge Payments', 'color' => '#ffc107', 'icon' => 'fa-handshake-o'],
            'bereavements' => ['label' => 'Bereavements', 'color' => '#6f42c1', 'icon' => 'fa-heart'],
        ];

        $breakdown = [];

        foreach ($meta as $key => $info) {
            $amount = $amounts[$key] ?? 0.0;
            $breakdown[] = [
                'key' => $key,
                'label' => $info['label'],
                'amount' => $amount,
                'percent' => $total > 0 ? round(($amount / $total) * 100, 1) : 0.0,
                'color' => $info['color'],
                'icon' => $info['icon'],
            ];
        }

        return $breakdown;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function incomeTrend(int $churchId, int $months): array
    {
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $bereavement = $this->bereavementIncomeBetween($churchId, $start, $end);
            $tithes = $this->approvedIncomeBetween(Tithe::class, 'tithe_date', $churchId, $start, $end);
            $offerings = $this->approvedIncomeBetween(Offering::class, 'offering_date', $churchId, $start, $end);
            $pledges = $this->approvedIncomeBetween(PledgePayment::class, 'payment_date', $churchId, $start, $end);

            $trend[] = [
                'month' => $start->format('M Y'),
                'short_month' => $start->format('M'),
                'income' => $bereavement + $tithes + $offerings + $pledges,
                'expenses' => $this->paidExpensesBetween($churchId, $start, $end),
            ];
        }

        return $trend;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function emptyTrend(int $months): array
    {
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $trend[] = [
                'month' => $start->format('M Y'),
                'expenses' => 0.0,
            ];
        }

        return $trend;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function recentTransactions(int $churchId, int $limit): Collection
    {
        $bereavement = BereavementContribution::query()
            ->with(['member', 'bereavementEvent'])
            ->where('has_contributed', true)
            ->whereHas('bereavementEvent', fn ($q) => $q->where('church_id', $churchId))
            ->latest('contribution_date')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (BereavementContribution $contribution) => [
                'type' => 'bereavement',
                'label' => 'Bereavement',
                'member' => $contribution->member?->full_name ?? 'Unknown',
                'description' => 'Contribution for '.$contribution->bereavementEvent?->deceased_name,
                'amount' => (float) $contribution->amount,
                'date' => $contribution->contribution_date,
                'icon' => 'fa-heart',
                'badge_class' => 'badge-danger',
                'route' => $contribution->bereavementEvent
                    ? route('church.bereavements.show', $contribution->bereavementEvent)
                    : null,
            ]);

        $tithes = Tithe::forChurch($churchId)
            ->approved()
            ->with('member')
            ->latest('tithe_date')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Tithe $tithe) => [
                'type' => 'tithe',
                'label' => 'Tithe',
                'member' => $tithe->member?->full_name ?? 'Unknown',
                'description' => 'Member tithe',
                'amount' => (float) $tithe->amount,
                'date' => $tithe->tithe_date,
                'icon' => 'fa-money',
                'badge_class' => 'badge-primary',
                'route' => route('church.tithes.show', $tithe),
            ]);

        $offerings = Offering::forChurch($churchId)
            ->approved()
            ->with(['member', 'churchService'])
            ->latest('offering_date')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Offering $offering) => [
                'type' => 'offering',
                'label' => 'Offering',
                'member' => $offering->contributorLabel(),
                'description' => $offering->offeringTypeLabel().' offering',
                'amount' => (float) $offering->amount,
                'date' => $offering->offering_date,
                'icon' => 'fa-gift',
                'badge_class' => 'badge-success',
                'route' => route('church.offerings.show', $offering),
            ]);

        $pledgePayments = PledgePayment::forChurch($churchId)
            ->approved()
            ->with(['pledge.member'])
            ->latest('payment_date')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (PledgePayment $payment) => [
                'type' => 'pledge_payment',
                'label' => 'Pledge Payment',
                'member' => $payment->pledge?->member?->full_name ?? 'Unknown',
                'description' => ($payment->pledge?->pledgeTypeLabel() ?? 'Pledge').' payment',
                'amount' => (float) $payment->amount,
                'date' => $payment->payment_date,
                'icon' => 'fa-handshake-o',
                'badge_class' => 'badge-warning',
                'route' => $payment->pledge
                    ? route('church.pledges.show', $payment->pledge)
                    : null,
            ]);

        $expenses = Expense::forChurch($churchId)
            ->where('status', ExpenseStatus::Paid->value)
            ->with('budget')
            ->latest('expense_date')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (Expense $expense) => [
                'type' => 'expense',
                'label' => 'Expense',
                'member' => $expense->budget?->budget_name ?? '—',
                'description' => $expense->expense_category->label().' expense',
                'amount' => (float) $expense->amount,
                'date' => $expense->expense_date,
                'icon' => 'fa-file-text-o',
                'badge_class' => 'badge-danger',
                'route' => route('church.expenses.show', $expense),
            ]);

        return $bereavement->merge($tithes)->merge($offerings)->merge($pledgePayments)->merge($expenses)
            ->sortByDesc(fn ($item) => $item['date']?->timestamp ?? 0)
            ->take($limit)
            ->values();
    }

    private function paidExpensesBetween(int $churchId, ?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = Expense::forChurch($churchId)
            ->where('status', ExpenseStatus::Paid->value);

        if ($from) {
            $query->whereDate('expense_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('expense_date', '<=', $to);
        }

        return (float) $query->sum('amount');
    }

    /**
     * @return Collection<int, BereavementEvent>
     */
    private function openBereavements(int $churchId): Collection
    {
        return BereavementEvent::forChurch($churchId)
            ->open()
            ->withSum(['contributions as total_raised' => fn ($q) => $q->where('has_contributed', true)], 'amount')
            ->withCount(['contributions as contributors_count' => fn ($q) => $q->where('has_contributed', true)])
            ->orderBy('contribution_end_date')
            ->limit(5)
            ->get();
    }

    private function contributorsThisMonth(int $churchId, Carbon $from, Carbon $to): int
    {
        return BereavementContribution::query()
            ->where('has_contributed', true)
            ->whereHas('bereavementEvent', fn ($q) => $q->where('church_id', $churchId))
            ->whereDate('contribution_date', '>=', $from)
            ->whereDate('contribution_date', '<=', $to)
            ->distinct()
            ->count('member_id');
    }

    private function averageBereavementContribution(int $churchId, Carbon $from, Carbon $to): float
    {
        $query = BereavementContribution::query()
            ->where('has_contributed', true)
            ->whereHas('bereavementEvent', fn ($q) => $q->where('church_id', $churchId))
            ->whereDate('contribution_date', '>=', $from)
            ->whereDate('contribution_date', '<=', $to);

        $count = (int) $query->count();

        if ($count === 0) {
            return 0.0;
        }

        return round((float) $query->sum('amount') / $count, 2);
    }

    private function percentChange(float $previous, float $current): ?float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
