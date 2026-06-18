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

            foreach ($memberIds as $memberId) {
                AttendanceRecord::create([
                    'church_id' => $church->id,
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
}
