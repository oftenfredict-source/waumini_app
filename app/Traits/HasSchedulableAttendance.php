<?php

namespace App\Traits;

use App\Enums\ChurchServiceStatus;
use App\Enums\SpecialEventStatus;
use App\Models\Church;
use App\Models\ChurchService;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

trait HasSchedulableAttendance
{
    public function churchTimezone(): string
    {
        $church = $this->relationLoaded('church')
            ? $this->church
            : ($this->church_id ? Church::query()->find($this->church_id) : null);

        $timezone = $church?->timezone;

        if (! $timezone || $timezone === 'UTC') {
            return 'Africa/Dar_es_Salaam';
        }

        return $timezone;
    }

    public function nowInChurch(): Carbon
    {
        return Carbon::now($this->churchTimezone());
    }

    /**
     * Combined scheduled start datetime in the church timezone.
     */
    public function attendanceOpensAt(): ?Carbon
    {
        return $this->scheduledDateTime($this->start_time, '00:00');
    }

    public function attendanceEndsAt(): ?Carbon
    {
        if (! $this->end_time) {
            return null;
        }

        return $this->scheduledDateTime($this->end_time);
    }

    /**
     * Attendance may be recorded from the scheduled start time onward (during or after),
     * except when the service/event is cancelled.
     */
    public function canRecordAttendance(?CarbonInterface $now = null): bool
    {
        if ($this->status?->value === 'cancelled') {
            return false;
        }

        $this->syncLiveStatus($now);

        $opensAt = $this->attendanceOpensAt();

        if (! $opensAt) {
            return false;
        }

        $now = $now
            ? Carbon::parse($now)->timezone($this->churchTimezone())
            : $this->nowInChurch();

        return $now->greaterThanOrEqualTo($opensAt);
    }

    /**
     * Move scheduled → ongoing when start time is reached, and ongoing → completed after end time.
     */
    public function syncLiveStatus(?CarbonInterface $now = null): void
    {
        if (! $this->exists || $this->status?->value === 'cancelled') {
            return;
        }

        $next = $this->computeLiveStatus($now);

        if (! $next || $this->status === $next) {
            return;
        }

        $this->status = $next;
        $this->newQuery()
            ->whereKey($this->getKey())
            ->update(['status' => $next->value]);
        $this->syncOriginalAttribute('status');
    }

    public function computeLiveStatus(?CarbonInterface $now = null): ChurchServiceStatus|SpecialEventStatus|null
    {
        $current = $this->status;

        if (! $current || in_array($current->value, ['cancelled', 'completed'], true)) {
            return $current;
        }

        $now = $now
            ? Carbon::parse($now)->timezone($this->churchTimezone())
            : $this->nowInChurch();

        $opensAt = $this->attendanceOpensAt();
        $endsAt = $this->attendanceEndsAt();

        if ($opensAt && $now->greaterThanOrEqualTo($opensAt)) {
            if ($endsAt && $now->greaterThanOrEqualTo($endsAt) && $current->value !== 'completed') {
                return $this->statusFromValue('completed');
            }

            if ($current->value === 'scheduled' && (! $endsAt || $now->lt($endsAt))) {
                return $this->statusFromValue('ongoing');
            }
        }

        return $current;
    }

    private function scheduledDateTime(mixed $time, string $fallbackTime = '00:00'): ?Carbon
    {
        $rawDate = $this->getAttribute('service_date') ?? $this->getAttribute('event_date');

        if (! $rawDate) {
            return null;
        }

        $dateString = Carbon::parse($rawDate)->toDateString();
        $clock = $time ? substr((string) $time, 0, 5) : $fallbackTime;

        if (! preg_match('/^\d{2}:\d{2}$/', $clock)) {
            $clock = $fallbackTime;
        }

        return Carbon::createFromFormat(
            'Y-m-d H:i',
            $dateString.' '.$clock,
            $this->churchTimezone()
        );
    }

    private function statusFromValue(string $value): ChurchServiceStatus|SpecialEventStatus
    {
        if ($this instanceof ChurchService) {
            return ChurchServiceStatus::from($value);
        }

        return SpecialEventStatus::from($value);
    }
}
