<?php

namespace App\Services\Church;

use App\Enums\AttendanceSourceType;
use App\Models\AttendanceRecord;
use App\Models\Church;
use App\Models\ChurchService;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Models\SpecialEvent;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function resolveSource(Church $church, string $sourceType, int $sourceId): ChurchService|SpecialEvent
    {
        return match ($sourceType) {
            AttendanceSourceType::ChurchService->value => ChurchService::forChurch($church->id)->whereKey($sourceId)->firstOrFail(),
            AttendanceSourceType::SpecialEvent->value => SpecialEvent::forChurch($church->id)->whereKey($sourceId)->firstOrFail(),
            default => throw new \InvalidArgumentException('Invalid attendance source type.'),
        };
    }

    public function attendanceMode(ChurchService|SpecialEvent $source): string
    {
        if ($source instanceof SpecialEvent) {
            return 'mixed';
        }

        return $source->isSundaySchool() ? 'sunday_school' : 'main_service';
    }

    /**
     * @return array{
     *     source: ChurchService|SpecialEvent,
     *     members_count: int,
     *     children_count: int,
     *     guests_count: int,
     *     total_count: int,
     *     records: Collection<int, AttendanceRecord>
     * }
     */
    public function summary(Church $church, string $sourceType, int $sourceId): array
    {
        $source = $this->resolveSource($church, $sourceType, $sourceId);

        $records = AttendanceRecord::forChurch($church->id)
            ->forSource($sourceType, $sourceId)
            ->with(['member', 'dependant', 'recorder'])
            ->orderBy('attended_at')
            ->get();

        $membersCount = $records->whereNotNull('member_id')->count();
        $childrenCount = $records->whereNotNull('dependant_id')->count();
        $guestsCount = (int) ($source->guests_count ?? 0);

        return [
            'source' => $source,
            'members_count' => $membersCount,
            'children_count' => $childrenCount,
            'guests_count' => $guestsCount,
            'total_count' => $membersCount + $childrenCount + $guestsCount,
            'records' => $records,
        ];
    }

    public function canRecordAttendance(ChurchService|SpecialEvent $source, ?\Carbon\CarbonInterface $now = null): bool
    {
        return $source->canRecordAttendance($now);
    }

    public function sync(
        Church $church,
        string $sourceType,
        int $sourceId,
        array $memberIds,
        array $dependantIds,
        int $guestsCount,
        ?string $notes,
        ?User $recorder = null,
    ): array {
        $source = $this->resolveSource($church, $sourceType, $sourceId);

        if (! $this->canRecordAttendance($source)) {
            $opensAt = $source->attendanceOpensAt();
            $when = $opensAt?->format('M d, Y H:i') ?? 'the scheduled start';

            throw \Illuminate\Validation\ValidationException::withMessages([
                'source_id' => [__('pages.attendance.not_yet_open', ['when' => $when])],
            ]);
        }

        $enumType = AttendanceSourceType::from($sourceType);

        return DB::transaction(function () use ($church, $source, $enumType, $sourceType, $sourceId, $memberIds, $dependantIds, $guestsCount, $notes, $recorder) {
            AttendanceRecord::forChurch($church->id)
                ->forSource($sourceType, $sourceId)
                ->delete();

            $mode = $this->attendanceMode($source);

            if ($mode === 'sunday_school') {
                $memberIds = collect();
                $dependantIds = MemberDependant::forChurch($church->id)
                    ->forSundaySchool()
                    ->whereIn('id', $dependantIds)
                    ->pluck('id');
            } elseif ($mode === 'main_service') {
                $memberIds = Member::forChurch($church->id)
                    ->whereIn('id', $memberIds)
                    ->where('status', 'active')
                    ->pluck('id');
                $dependantIds = MemberDependant::forChurch($church->id)
                    ->forMainServiceAttendance()
                    ->whereIn('id', $dependantIds)
                    ->pluck('id');
            } else {
                $memberIds = Member::forChurch($church->id)
                    ->whereIn('id', $memberIds)
                    ->where('status', 'active')
                    ->pluck('id');
                $dependantIds = MemberDependant::forChurch($church->id)
                    ->children()
                    ->whereNull('linked_member_id')
                    ->whereIn('id', $dependantIds)
                    ->pluck('id');
            }

            $now = now();
            $branchId = $source->branch_id;

            foreach ($memberIds as $memberId) {
                AttendanceRecord::create([
                    'church_id' => $church->id,
                    'branch_id' => $branchId,
                    'source_type' => $enumType,
                    'source_id' => $sourceId,
                    'member_id' => $memberId,
                    'attended_at' => $now,
                    'recorded_by' => $recorder?->id,
                    'notes' => $notes,
                ]);
            }

            foreach ($dependantIds as $dependantId) {
                AttendanceRecord::create([
                    'church_id' => $church->id,
                    'branch_id' => $branchId,
                    'source_type' => $enumType,
                    'source_id' => $sourceId,
                    'dependant_id' => $dependantId,
                    'attended_at' => $now,
                    'recorded_by' => $recorder?->id,
                    'notes' => $notes,
                ]);
            }

            $source->update(['guests_count' => $guestsCount]);

            return [
                'members_count' => $memberIds->count(),
                'children_count' => $dependantIds->count(),
                'guests_count' => $guestsCount,
                'total_count' => $memberIds->count() + $dependantIds->count() + $guestsCount,
            ];
        });
    }

    public function sourceLabel(ChurchService|SpecialEvent $source): string
    {
        if ($source instanceof ChurchService) {
            return $source->displayTitle().' — '.$source->service_date->format('M d, Y');
        }

        return $source->title.' — '.$source->event_date->format('M d, Y');
    }

    public function attendanceCountFor(Church $church, string $sourceType, int $sourceId): int
    {
        $summary = $this->summary($church, $sourceType, $sourceId);

        return $summary['total_count'];
    }

    /**
     * Services (and events) that already have attendance recorded.
     *
     * @return array{
     *     start: \Illuminate\Support\Carbon,
     *     end: \Illuminate\Support\Carbon,
     *     totals: array{services: int, member_marks: int, child_marks: int, guests: int, average_members: float},
     *     sunday_totals: array{services: int, member_marks: int, guests: int, average_members: float, active_members: int, avg_rate: float},
     *     services: \Illuminate\Support\Collection<int, array<string, mixed>>,
     *     missing_members: \Illuminate\Support\Collection<int, array<string, mixed>>,
     *     recent_sundays: \Illuminate\Support\Collection<int, ChurchService>
     * }
     */
    public function statistics(Church $church, ?\Illuminate\Support\Carbon $start = null, ?\Illuminate\Support\Carbon $end = null): array
    {
        $end = ($end ?? now())->copy()->endOfDay();
        $start = ($start ?? now()->subMonths(3))->copy()->startOfDay();

        $services = ChurchService::forChurch($church->id)
            ->where('status', '!=', \App\Enums\ChurchServiceStatus::Cancelled->value)
            ->whereBetween('service_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->get();

        $serviceRows = collect();
        $memberMarks = 0;
        $childMarks = 0;
        $guestsTotal = 0;
        $recordedServices = 0;

        $sundayMemberMarks = 0;
        $sundayGuests = 0;
        $sundayRecorded = 0;

        $activeMembersCount = Member::forChurch($church->id)->activeMembers()->count();

        foreach ($services as $service) {
            $summary = $this->summary($church, AttendanceSourceType::ChurchService->value, $service->id);
            $hasAttendance = $summary['total_count'] > 0;

            if (! $hasAttendance) {
                continue;
            }

            $recordedServices++;
            $memberMarks += $summary['members_count'];
            $childMarks += $summary['children_count'];
            $guestsTotal += $summary['guests_count'];

            $isSunday = $service->service_type === \App\Enums\ChurchServiceType::Sunday;
            if ($isSunday) {
                $sundayRecorded++;
                $sundayMemberMarks += $summary['members_count'];
                $sundayGuests += $summary['guests_count'];
            }

            $rate = $activeMembersCount > 0
                ? round(($summary['members_count'] / $activeMembersCount) * 100, 1)
                : 0.0;

            $serviceRows->push([
                'id' => $service->id,
                'date' => $service->service_date,
                'title' => $service->displayTitle(),
                'type' => $service->service_type->label(),
                'is_sunday' => $isSunday,
                'members_count' => $summary['members_count'],
                'children_count' => $summary['children_count'],
                'guests_count' => $summary['guests_count'],
                'total_count' => $summary['total_count'],
                'attendance_rate' => $rate,
            ]);
        }

        $recentSundays = $this->recordedSundayServices($church)->take(3)->values();
        $missingMembers = $this->membersMissingRecentSundays($church, $recentSundays);

        return [
            'start' => $start,
            'end' => $end,
            'totals' => [
                'services' => $recordedServices,
                'member_marks' => $memberMarks,
                'child_marks' => $childMarks,
                'guests' => $guestsTotal,
                'average_members' => $recordedServices > 0
                    ? round($memberMarks / $recordedServices, 1)
                    : 0.0,
            ],
            'sunday_totals' => [
                'services' => $sundayRecorded,
                'member_marks' => $sundayMemberMarks,
                'guests' => $sundayGuests,
                'average_members' => $sundayRecorded > 0
                    ? round($sundayMemberMarks / $sundayRecorded, 1)
                    : 0.0,
                'active_members' => $activeMembersCount,
                'avg_rate' => ($sundayRecorded > 0 && $activeMembersCount > 0)
                    ? round(($sundayMemberMarks / $sundayRecorded / $activeMembersCount) * 100, 1)
                    : 0.0,
            ],
            'services' => $serviceRows,
            'missing_members' => $missingMembers,
            'recent_sundays' => $recentSundays,
        ];
    }

    /**
     * @return Collection<int, ChurchService>
     */
    public function recordedSundayServices(Church $church, int $limit = 12): Collection
    {
        $sourceType = AttendanceSourceType::ChurchService->value;

        return ChurchService::forChurch($church->id)
            ->where('service_type', \App\Enums\ChurchServiceType::Sunday->value)
            ->where('status', '!=', \App\Enums\ChurchServiceStatus::Cancelled->value)
            ->whereDate('service_date', '<=', now()->toDateString())
            ->where(function ($query) use ($sourceType) {
                $query->where('guests_count', '>', 0)
                    ->orWhereExists(function ($sub) use ($sourceType) {
                        $sub->selectRaw('1')
                            ->from('attendance_records')
                            ->whereColumn('attendance_records.source_id', 'church_services.id')
                            ->where('attendance_records.source_type', $sourceType);
                    });
            })
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Collection<int, ChurchService>  $sundays
     * @return Collection<int, array<string, mixed>>
     */
    public function membersMissingRecentSundays(Church $church, Collection $sundays): Collection
    {
        if ($sundays->isEmpty()) {
            return collect();
        }

        $serviceIds = $sundays->pluck('id')->all();
        $attended = AttendanceRecord::forChurch($church->id)
            ->membersOnly()
            ->where('source_type', AttendanceSourceType::ChurchService->value)
            ->whereIn('source_id', $serviceIds)
            ->get(['member_id', 'source_id'])
            ->groupBy('member_id');

        return Member::forChurch($church->id)
            ->activeMembers()
            ->orderByRaw('CASE WHEN envelope_number IS NULL OR envelope_number = "" THEN 1 ELSE 0 END')
            ->orderBy('envelope_number')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'envelope_number', 'phone_number'])
            ->map(function (Member $member) use ($sundays, $attended) {
                $presentIds = $attended->get($member->id)?->pluck('source_id')->all() ?? [];
                $missed = $sundays->filter(fn (ChurchService $service) => ! in_array($service->id, $presentIds, true));
                $consecutive = 0;

                foreach ($sundays as $service) {
                    if (in_array($service->id, $presentIds, true)) {
                        break;
                    }
                    $consecutive++;
                }

                if ($consecutive === 0) {
                    return null;
                }

                return [
                    'id' => $member->id,
                    'full_name' => $member->full_name,
                    'envelope_number' => $member->envelope_number,
                    'phone_number' => $member->phone_number,
                    'missed_count' => $missed->count(),
                    'consecutive_misses' => $consecutive,
                ];
            })
            ->filter()
            ->sortByDesc('consecutive_misses')
            ->values();
    }
}

