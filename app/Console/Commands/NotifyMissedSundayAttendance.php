<?php

namespace App\Console\Commands;

use App\Models\Church;
use App\Services\Church\MissedAttendanceNotificationService;
use Illuminate\Console\Command;

class NotifyMissedSundayAttendance extends Command
{
    protected $signature = 'attendance:notify-missed-sundays {--church= : Church ID to limit processing}';

    protected $description = 'SMS members who missed the last 3 recorded Sunday services';

    public function handle(MissedAttendanceNotificationService $service): int
    {
        $this->info('Checking members who missed '.MissedAttendanceNotificationService::MISS_THRESHOLD.' Sunday services...');

        $churches = Church::query()
            ->when($this->option('church'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        $totalNotified = 0;

        foreach ($churches as $church) {
            $result = $service->processChurch($church);
            $totalNotified += $result['notified'];

            if ($result['notified'] > 0) {
                $this->line("Church #{$church->id} ({$church->name}): notified {$result['notified']} member(s).");
            }
        }

        $this->info($totalNotified > 0
            ? "Done. Sent {$totalNotified} reminder SMS."
            : 'No missed-Sunday reminders to send.');

        return self::SUCCESS;
    }
}
