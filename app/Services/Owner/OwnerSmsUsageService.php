<?php

namespace App\Services\Owner;

use App\Models\Church;
use App\Models\SmsLog;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OwnerSmsUsageService
{
    /**
     * @return Collection<int, array{
     *     church: Church,
     *     messages_count: int,
     *     segments_used: int,
     *     limit: int|null,
     *     usage_percent: float|null
     * }>
     */
    public function churchesUsage(?CarbonInterface $month = null): Collection
    {
        [$start, $end] = $this->monthRange($month);

        $usageByChurch = SmsLog::query()
            ->where('status', 'sent')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('church_id')
            ->selectRaw('church_id, COUNT(*) as messages_count, COALESCE(SUM(segments), 0) as segments_used')
            ->groupBy('church_id')
            ->get()
            ->keyBy('church_id');

        return Church::query()
            ->with(['activeSubscription.package'])
            ->orderBy('name')
            ->get()
            ->map(function (Church $church) use ($usageByChurch) {
                $row = $usageByChurch->get($church->id);
                $messagesCount = (int) ($row->messages_count ?? 0);
                $segmentsUsed = (int) ($row->segments_used ?? 0);
                $limit = $church->activeSubscription?->package?->max_sms_monthly;

                return [
                    'church' => $church,
                    'messages_count' => $messagesCount,
                    'segments_used' => $segmentsUsed,
                    'limit' => $limit,
                    'usage_percent' => $limit && $limit > 0
                        ? min(100, round(($segmentsUsed / $limit) * 100, 1))
                        : null,
                ];
            });
    }

    /**
     * @return array{
     *     messages_count: int,
     *     segments_used: int,
     *     limit: int|null,
     *     usage_percent: float|null
     * }
     */
    public function churchSummary(Church $church, ?CarbonInterface $month = null): array
    {
        [$start, $end] = $this->monthRange($month);

        $stats = SmsLog::query()
            ->where('church_id', $church->id)
            ->where('status', 'sent')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(*) as messages_count, COALESCE(SUM(segments), 0) as segments_used')
            ->first();

        $segmentsUsed = (int) ($stats->segments_used ?? 0);
        $limit = $church->activeSubscription?->package?->max_sms_monthly;

        return [
            'messages_count' => (int) ($stats->messages_count ?? 0),
            'segments_used' => $segmentsUsed,
            'limit' => $limit,
            'usage_percent' => $limit && $limit > 0
                ? min(100, round(($segmentsUsed / $limit) * 100, 1))
                : null,
        ];
    }

    public function churchMessages(
        Church $church,
        ?CarbonInterface $month = null,
        int $perPage = 25,
    ): LengthAwarePaginator {
        [$start, $end] = $this->monthRange($month);

        return SmsLog::query()
            ->where('church_id', $church->id)
            ->where('status', 'sent')
            ->whereBetween('created_at', [$start, $end])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function platformTotals(?CarbonInterface $month = null): array
    {
        [$start, $end] = $this->monthRange($month);

        $stats = SmsLog::query()
            ->where('status', 'sent')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('church_id')
            ->selectRaw('COUNT(DISTINCT church_id) as churches_count, COUNT(*) as messages_count, COALESCE(SUM(segments), 0) as segments_used')
            ->first();

        return [
            'churches_count' => (int) ($stats->churches_count ?? 0),
            'messages_count' => (int) ($stats->messages_count ?? 0),
            'segments_used' => (int) ($stats->segments_used ?? 0),
        ];
    }

    /**
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}
     */
    private function monthRange(?CarbonInterface $month): array
    {
        $reference = $month?->copy() ?? now();

        return [
            $reference->copy()->startOfMonth(),
            $reference->copy()->endOfMonth(),
        ];
    }
}
