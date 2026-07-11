<?php

namespace App\Traits;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

trait HasSchedulableAttendance
{
    /**
     * Combined scheduled start datetime for attendance gating.
     * Uses start_time when set; otherwise the beginning of the scheduled date.
     */
    public function attendanceOpensAt(): ?Carbon
    {
        $rawDate = $this->getAttribute('service_date') ?? $this->getAttribute('event_date');

        if (! $rawDate) {
            return null;
        }

        $date = Carbon::parse($rawDate)->startOfDay();
        $time = $this->start_time
            ? substr((string) $this->start_time, 0, 5)
            : '00:00';

        return $date->setTimeFromTimeString($time);
    }

    /**
     * Attendance may be recorded from the scheduled start time onward (during or after).
     */
    public function canRecordAttendance(?CarbonInterface $now = null): bool
    {
        $opensAt = $this->attendanceOpensAt();

        if (! $opensAt) {
            return false;
        }

        return ($now ?? now())->greaterThanOrEqualTo($opensAt);
    }
}
