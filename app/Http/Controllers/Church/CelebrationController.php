<?php

namespace App\Http\Controllers\Church;

use App\Enums\CelebrationSource;
use App\Enums\CelebrationStatus;
use App\Enums\CelebrationType;
use App\Enums\WeddingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreCelebrationRequest;
use App\Http\Requests\Church\UpdateCelebrationRequest;
use App\Models\Celebration;
use App\Models\Member;
use App\Services\Church\CelebrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CelebrationController extends Controller
{
    public function __construct(
        private readonly CelebrationService $celebrationService,
    ) {
        $this->authorizeResource(Celebration::class, 'celebration');
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;
        $this->celebrationService->syncForChurch($church);

        $year = $request->filled('year') ? $request->integer('year') : now()->year;
        $isCurrentYear = $year === (int) now()->year;
        $displayMonth = $isCurrentYear ? (int) now()->month : (int) ($request->integer('month') ?: 1);

        $period = $request->string('period')->trim()->toString();
        if ($period === '') {
            $period = 'this_month';
        }

        $month = $request->filled('month') ? $request->integer('month') : null;

        $query = Celebration::forChurch($church->id)
            ->with(['member', 'creator'])
            ->forCelebrationYear($year)
            ->orderBy('celebration_date')
            ->orderBy('title');

        if ($type = $request->string('celebration_type')->trim()->toString()) {
            $query->where('celebration_type', $type);
        }

        if ($source = $request->string('source')->trim()->toString()) {
            $query->where('source', $source);
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        } else {
            $query->where('status', CelebrationStatus::Upcoming);
        }

        $hasCustomRange = $request->filled('from') || $request->filled('to');

        if ($hasCustomRange) {
            if ($from = $request->string('from')->trim()->toString()) {
                $query->whereDate('celebration_date', '>=', $from);
            }
            if ($to = $request->string('to')->trim()->toString()) {
                $query->whereDate('celebration_date', '<=', $to);
            }
        } else {
            $query->notPassedYet($year);

            if ($period === 'month' && $month >= 1 && $month <= 12) {
                $query->inEventMonth($month);
            } elseif ($period === 'this_month') {
                $query->inEventMonth($isCurrentYear ? now()->month : $displayMonth);
            } elseif ($period === 'next_30_days' && $isCurrentYear) {
                $query->withinDays(30);
            }
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('member', fn ($mq) => $mq->where('full_name', 'like', "%{$search}%"));
            });
        }

        $base = Celebration::forChurch($church->id)
            ->where('status', CelebrationStatus::Upcoming)
            ->forCelebrationYear($year)
            ->notPassedYet($year);

        $monthOptions = collect(range(1, 12))->mapWithKeys(fn (int $m) => [
            $m => \Carbon\Carbon::createFromDate(2000, $m, 1)->format('F'),
        ]);

        $monthName = $monthOptions[$displayMonth] ?? '';
        $yearMonthLabel = $monthName.' '.$year;

        $periodLabel = match (true) {
            $hasCustomRange => 'Custom date range — '.$year,
            $period === 'month' && $month => ($monthOptions[$month] ?? '').' '.$year,
            $period === 'this_month' => $yearMonthLabel,
            $period === 'next_30_days' => 'Next 30 days ('.$year.')',
            $period === 'all' && $request->string('celebration_type')->toString() === CelebrationType::Birthday->value => 'Birthdays — '.$yearMonthLabel,
            $period === 'all' && $request->string('celebration_type')->toString() === CelebrationType::WeddingAnniversary->value => 'Anniversaries — '.$yearMonthLabel,
            $period === 'all' => 'Remaining in '.$year,
            default => $yearMonthLabel,
        };

        $statsBase = fn () => Celebration::forChurch($church->id)
            ->where('status', CelebrationStatus::Upcoming)
            ->forCelebrationYear($year)
            ->notPassedYet($year)
            ->inEventMonth($displayMonth);

        return view('church.celebrations.index', [
            'celebrations' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'celebration_type', 'source', 'status', 'from', 'to', 'period', 'month', 'year']),
            'period' => $period,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'displayMonth' => $displayMonth,
            'isCurrentYear' => $isCurrentYear,
            'periodLabel' => $periodLabel,
            'yearOptions' => range(now()->year - 1, now()->year + 1),
            'types' => CelebrationType::cases(),
            'sources' => CelebrationSource::cases(),
            'statuses' => CelebrationStatus::cases(),
            'monthOptions' => $monthOptions,
            'stats' => [
                'next_30_days' => $isCurrentYear
                    ? (clone $base)->withinDays(30)->count()
                    : 0,
                'this_month' => $statsBase()->count(),
                'birthdays' => $statsBase()->where('celebration_type', CelebrationType::Birthday)->count(),
                'anniversaries' => $statsBase()->where('celebration_type', CelebrationType::WeddingAnniversary)->count(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('church.celebrations.create', $this->formData($request->user()->church->id));
    }

    public function store(StoreCelebrationRequest $request): RedirectResponse
    {
        $celebration = $this->celebrationService->createManual(
            $request->user()->church,
            $request->validated(),
            $request->user(),
        );

        return redirect()
            ->route('church.celebrations.show', $celebration)
            ->with('success', 'Celebration created successfully.');
    }

    public function show(Celebration $celebration): View
    {
        $celebration->load(['member', 'creator']);

        return view('church.celebrations.show', [
            'celebration' => $celebration,
        ]);
    }

    public function edit(Celebration $celebration): View
    {
        return view('church.celebrations.edit', array_merge(
            ['celebration' => $celebration->load('member')],
            $this->formData($celebration->church_id),
        ));
    }

    public function update(UpdateCelebrationRequest $request, Celebration $celebration): RedirectResponse
    {
        $this->celebrationService->update($celebration, $request->validated());

        return redirect()
            ->route('church.celebrations.show', $celebration)
            ->with('success', 'Celebration updated successfully.');
    }

    public function destroy(Celebration $celebration): RedirectResponse
    {
        $title = $celebration->title;
        $this->celebrationService->delete($celebration);

        return redirect()
            ->route('church.celebrations.index')
            ->with('success', 'Celebration "'.$title.'" removed.');
    }

    public function sync(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', Celebration::class);
        $this->celebrationService->syncForChurch($request->user()->church);

        return back()->with('success', 'Celebrations refreshed from member profiles.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(int $churchId): array
    {
        return [
            'members' => Member::forChurch($churchId)
                ->activeMembers()
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'member_number', 'date_of_birth', 'wedding_date']),
            'types' => CelebrationType::cases(),
            'statuses' => CelebrationStatus::cases(),
            'weddingTypes' => WeddingType::cases(),
        ];
    }
}
