<?php

namespace App\Services\Owner;

use App\Enums\ChurchStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Church;
use App\Models\ChurchSubscription;
use App\Models\Payment;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function overview(): array
    {
        $totalChurches = Church::count();
        $activeChurches = Church::where('status', ChurchStatus::Active)->count();
        $trialChurches = Church::where('status', ChurchStatus::Trial)->count();
        $suspendedChurches = Church::where('status', ChurchStatus::Suspended)->count();
        $expiredChurches = Church::where('status', ChurchStatus::Expired)->count();
        $newSignups30d = Church::where('created_at', '>=', now()->subDays(30))->count();
        $newSignups7d = Church::where('created_at', '>=', now()->subDays(7))->count();

        return [
            'total_churches' => $totalChurches,
            'active_churches' => $activeChurches + $trialChurches,
            'trial_churches' => $trialChurches,
            'suspended_churches' => $suspendedChurches,
            'expired_churches' => $expiredChurches,
            'new_signups_30d' => $newSignups30d,
            'new_signups_7d' => $newSignups7d,
            'mrr' => round($this->collectedThisMonth(), 2),
            'arr' => round($this->collectedLastTwelveMonths(), 2),
            'paying_churches' => $this->payingChurchesCount(),
            'currency' => SystemSetting::platformCurrency(),
        ];
    }

    public function signupsChart(int $months = 12): Collection
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        $signups = Church::query()
            ->where('created_at', '>=', $start)
            ->get()
            ->groupBy(fn (Church $church) => $church->created_at->format('Y-m'))
            ->map->count();

        $labels = collect();
        $data = collect();

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i)->format('Y-m');
            $labels->push(Carbon::createFromFormat('Y-m', $month)->format('M Y'));
            $data->push($signups->get($month, 0));
        }

        return collect([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    public function statusBreakdown(): Collection
    {
        return Church::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
    }

    public function recentChurches(int $limit = 5): Collection
    {
        return Church::with(['activeSubscription.package', 'primaryDomain'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function churchesByPackage(): Collection
    {
        return ChurchSubscription::query()
            ->whereIn('status', [SubscriptionStatus::Trial, SubscriptionStatus::Active])
            ->with('package')
            ->get()
            ->groupBy(fn ($sub) => $sub->package?->name ?? 'Unknown')
            ->map->count();
    }

    public function monthlyRevenueChart(int $months = 12): Collection
    {
        $start = now()->subMonths($months - 1)->startOfMonth();

        $payments = \App\Models\Payment::query()
            ->where('status', 'completed')
            ->where('paid_at', '>=', $start)
            ->get()
            ->groupBy(fn ($p) => $p->paid_at->format('Y-m'))
            ->map(fn ($group) => $group->sum('amount'));

        $labels = collect();
        $data = collect();

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i)->format('Y-m');
            $labels->push(Carbon::createFromFormat('Y-m', $month)->format('M Y'));
            $data->push(round((float) $payments->get($month, 0), 2));
        }

        return collect(['labels' => $labels, 'data' => $data]);
    }

    private function collectedThisMonth(): float
    {
        return (float) Payment::query()
            ->where('status', 'completed')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
    }

    private function collectedLastTwelveMonths(): float
    {
        return (float) Payment::query()
            ->where('status', 'completed')
            ->where('paid_at', '>=', now()->subMonths(12)->startOfMonth())
            ->sum('amount');
    }

    private function payingChurchesCount(): int
    {
        return (int) Payment::query()
            ->where('status', 'completed')
            ->distinct()
            ->count('church_id');
    }
}
