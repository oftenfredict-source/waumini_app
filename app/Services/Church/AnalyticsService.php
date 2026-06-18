<?php

namespace App\Services\Church;

use App\Enums\ExpenseStatus;
use App\Enums\MemberStatus;
use App\Enums\MemberType;
use App\Enums\MembershipType;
use App\Models\AttendanceRecord;
use App\Models\BereavementContribution;
use App\Models\Church;
use App\Models\ChurchService;
use App\Models\Expense;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Models\Offering;
use App\Models\PledgePayment;
use App\Models\SpecialEvent;
use App\Models\Tithe;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function build(Church $church): array
    {
        $churchId = $church->id;
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $monthlyIncome = $this->totalIncomeBetween($churchId, $monthStart, $monthEnd);
        $monthlyAttendance = AttendanceRecord::forChurch($churchId)
            ->whereBetween('attended_at', [$monthStart, $monthEnd])
            ->count();

        return [
            'currency' => $church->currency ?? 'TZS',
            'overview' => [
                'total_members' => Member::forChurch($churchId)->count(),
                'active_members' => Member::forChurch($churchId)->where('status', MemberStatus::Active->value)->count(),
                'children' => MemberDependant::forChurch($churchId)->count(),
                'monthly_income' => $monthlyIncome,
                'monthly_attendance' => $monthlyAttendance,
                'services_total' => ChurchService::forChurch($churchId)->count(),
                'special_events_total' => SpecialEvent::forChurch($churchId)->count(),
            ],
            'financial' => $this->financialAnalytics($churchId),
            'members' => $this->memberAnalytics($churchId),
            'attendance' => $this->attendanceAnalytics($churchId),
            'events' => $this->eventAnalytics($churchId),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function financialAnalytics(int $churchId): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $tithesAll = $this->approvedIncomeBetween(Tithe::class, 'tithe_date', $churchId);
        $offeringsAll = $this->approvedIncomeBetween(Offering::class, 'offering_date', $churchId);
        $pledgesAll = $this->approvedIncomeBetween(PledgePayment::class, 'payment_date', $churchId);
        $bereavementsAll = $this->bereavementIncomeBetween($churchId);
        $expensesAll = $this->paidExpensesBetween($churchId);
        $totalIncome = $tithesAll + $offeringsAll + $pledgesAll + $bereavementsAll;

        $monthlyTithes = $this->approvedIncomeBetween(Tithe::class, 'tithe_date', $churchId, $monthStart, $monthEnd);
        $monthlyOfferings = $this->approvedIncomeBetween(Offering::class, 'offering_date', $churchId, $monthStart, $monthEnd);
        $monthlyPledges = $this->approvedIncomeBetween(PledgePayment::class, 'payment_date', $churchId, $monthStart, $monthEnd);
        $monthlyBereavements = $this->bereavementIncomeBetween($churchId, $monthStart, $monthEnd);
        $monthlyExpenses = $this->paidExpensesBetween($churchId, $monthStart, $monthEnd);
        $monthlyIncome = $monthlyTithes + $monthlyOfferings + $monthlyPledges + $monthlyBereavements;

        $monthlyTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = $now->copy()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $income = $this->totalIncomeBetween($churchId, $start, $end);
            $expenses = $this->paidExpensesBetween($churchId, $start, $end);

            $monthlyTrends[] = [
                'month' => $start->format('M Y'),
                'short_month' => $start->format('M'),
                'income' => $income,
                'expenses' => $expenses,
                'net' => $income - $expenses,
            ];
        }

        $incomeBreakdown = $this->buildIncomeBreakdown([
            'tithes' => $monthlyTithes,
            'offerings' => $monthlyOfferings,
            'pledges' => $monthlyPledges,
            'bereavements' => $monthlyBereavements,
        ], $monthlyIncome);

        return [
            'totals' => [
                'income' => $totalIncome,
                'expenses' => $expensesAll,
                'net' => $totalIncome - $expensesAll,
                'tithes' => $tithesAll,
                'offerings' => $offeringsAll,
                'pledges' => $pledgesAll,
                'bereavements' => $bereavementsAll,
            ],
            'monthly' => [
                'income' => $monthlyIncome,
                'expenses' => $monthlyExpenses,
                'net' => $monthlyIncome - $monthlyExpenses,
            ],
            'monthly_trends' => $monthlyTrends,
            'income_breakdown' => $incomeBreakdown,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function memberAnalytics(int $churchId): array
    {
        $members = Member::forChurch($churchId)->get(['gender', 'member_type', 'membership_type', 'date_of_birth', 'created_at']);

        $gender = $members->groupBy(fn (Member $member) => strtolower((string) $member->gender))
            ->map->count()
            ->only(['male', 'female']);

        $memberTypes = $members->groupBy(fn (Member $member) => $member->member_type?->value ?? 'unknown')
            ->map->count()
            ->mapWithKeys(fn (int $count, string $key) => [
                MemberType::tryFrom($key)?->label() ?? ucfirst($key) => $count,
            ]);

        $membershipTypes = $members->groupBy(fn (Member $member) => $member->membership_type?->value ?? 'unknown')
            ->map->count()
            ->mapWithKeys(fn (int $count, string $key) => [
                MembershipType::tryFrom($key)?->label() ?? ucfirst($key) => $count,
            ]);

        $monthlyRegistrations = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $monthlyRegistrations[] = [
                'month' => $start->format('M Y'),
                'short_month' => $start->format('M'),
                'count' => $members->filter(fn (Member $member) => $member->created_at?->between($start, $end))->count(),
            ];
        }

        $ageGroups = [
            'Under 18' => 0,
            '18-25' => 0,
            '26-35' => 0,
            '36-50' => 0,
            '51-65' => 0,
            '65+' => 0,
            'Unknown' => 0,
        ];

        foreach ($members as $member) {
            if (! $member->date_of_birth) {
                $ageGroups['Unknown']++;
                continue;
            }

            $age = $member->date_of_birth->age;
            $bucket = match (true) {
                $age < 18 => 'Under 18',
                $age <= 25 => '18-25',
                $age <= 35 => '26-35',
                $age <= 50 => '36-50',
                $age <= 65 => '51-65',
                default => '65+',
            };
            $ageGroups[$bucket]++;
        }

        return [
            'totals' => [
                'total' => $members->count(),
                'male' => (int) ($gender['male'] ?? 0),
                'female' => (int) ($gender['female'] ?? 0),
            ],
            'gender' => $gender->toArray(),
            'member_types' => $memberTypes->toArray(),
            'membership_types' => $membershipTypes->toArray(),
            'monthly_registrations' => $monthlyRegistrations,
            'age_groups' => array_filter($ageGroups, fn (int $count) => $count > 0),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attendanceAnalytics(int $churchId): array
    {
        $monthlyTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $monthlyTrends[] = [
                'month' => $start->format('M Y'),
                'short_month' => $start->format('M'),
                'count' => AttendanceRecord::forChurch($churchId)
                    ->whereBetween('attended_at', [$start, $end])
                    ->count(),
            ];
        }

        $topMembers = AttendanceRecord::forChurch($churchId)
            ->whereNotNull('member_id')
            ->where('attended_at', '>=', now()->subDays(30))
            ->selectRaw('member_id, COUNT(*) as attendance_count')
            ->groupBy('member_id')
            ->orderByDesc('attendance_count')
            ->limit(8)
            ->with('member')
            ->get()
            ->map(fn (AttendanceRecord $record) => [
                'name' => $record->member?->full_name ?? 'Unknown',
                'type' => 'Member',
                'count' => (int) $record->attendance_count,
            ]);

        $topChildren = AttendanceRecord::forChurch($churchId)
            ->whereNotNull('dependant_id')
            ->where('attended_at', '>=', now()->subDays(30))
            ->selectRaw('dependant_id, COUNT(*) as attendance_count')
            ->groupBy('dependant_id')
            ->orderByDesc('attendance_count')
            ->limit(8)
            ->with('dependant')
            ->get()
            ->map(fn (AttendanceRecord $record) => [
                'name' => $record->dependant?->full_name ?? 'Unknown',
                'type' => 'Child',
                'count' => (int) $record->attendance_count,
            ]);

        $topAttendees = $topMembers->concat($topChildren)
            ->sortByDesc('count')
            ->take(10)
            ->values();

        $recentServices = ChurchService::forChurch($churchId)
            ->where('service_date', '>=', now()->subMonths(3)->toDateString())
            ->withCount('attendanceRecords')
            ->get();

        $averageAttendance = $recentServices->count() > 0
            ? round($recentServices->sum('attendance_records_count') / $recentServices->count(), 1)
            : 0.0;

        return [
            'total' => AttendanceRecord::forChurch($churchId)->count(),
            'monthly_trends' => $monthlyTrends,
            'top_attendees' => $topAttendees,
            'average_per_service' => $averageAttendance,
            'recent_services_count' => $recentServices->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventAnalytics(int $churchId): array
    {
        $today = now()->toDateString();

        $monthlyTrends = [];
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subMonths($i)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $services = ChurchService::forChurch($churchId)
                ->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
                ->count();

            $events = SpecialEvent::forChurch($churchId)
                ->whereBetween('event_date', [$start->toDateString(), $end->toDateString()])
                ->count();

            $monthlyTrends[] = [
                'month' => $start->format('M Y'),
                'short_month' => $start->format('M'),
                'services' => $services,
                'special_events' => $events,
                'total' => $services + $events,
            ];
        }

        return [
            'services' => [
                'total' => ChurchService::forChurch($churchId)->count(),
                'upcoming' => ChurchService::forChurch($churchId)->where('service_date', '>=', $today)->count(),
                'past' => ChurchService::forChurch($churchId)->where('service_date', '<', $today)->count(),
            ],
            'special_events' => [
                'total' => SpecialEvent::forChurch($churchId)->count(),
                'upcoming' => SpecialEvent::forChurch($churchId)->where('event_date', '>=', $today)->count(),
                'past' => SpecialEvent::forChurch($churchId)->where('event_date', '<', $today)->count(),
            ],
            'monthly_trends' => $monthlyTrends,
        ];
    }

    private function totalIncomeBetween(int $churchId, Carbon $from, Carbon $to): float
    {
        return $this->approvedIncomeBetween(Tithe::class, 'tithe_date', $churchId, $from, $to)
            + $this->approvedIncomeBetween(Offering::class, 'offering_date', $churchId, $from, $to)
            + $this->approvedIncomeBetween(PledgePayment::class, 'payment_date', $churchId, $from, $to)
            + $this->bereavementIncomeBetween($churchId, $from, $to);
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

    private function paidExpensesBetween(int $churchId, ?Carbon $from = null, ?Carbon $to = null): float
    {
        $query = Expense::forChurch($churchId)->where('status', ExpenseStatus::Paid->value);

        if ($from) {
            $query->whereDate('expense_date', '>=', $from);
        }

        if ($to) {
            $query->whereDate('expense_date', '<=', $to);
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
            'tithes' => ['label' => 'Tithes', 'color' => '#940000'],
            'offerings' => ['label' => 'Offerings', 'color' => '#28a745'],
            'pledges' => ['label' => 'Pledge Payments', 'color' => '#ffc107'],
            'bereavements' => ['label' => 'Bereavements', 'color' => '#6f42c1'],
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
            ];
        }

        return $breakdown;
    }
}
