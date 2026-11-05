<?php

namespace App\Services\Church;

use App\Enums\AttendanceSourceType;
use App\Enums\ChurchServiceStatus;
use App\Enums\ChurchServiceType;
use App\Models\AttendanceAbsenceNotification;
use App\Models\AttendanceRecord;
use App\Models\Church;
use App\Models\ChurchService;
use App\Models\Member;
use App\Services\Sms\ChurchSmsService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MissedAttendanceNotificationService
{
    public const MISS_THRESHOLD = 3;

    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly ChurchSmsService $sms,
        private readonly ChurchSettingsService $churchSettings,
    ) {}

    /**
     * Run once per church per day when someone uses the church system.
     * The 08:00 scheduler remains; this covers servers where cron is not running.
     */
    public function dispatchDaily(Church $church): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $key = 'missed-sunday-sms:'.$church->id.':'.now()->toDateString();

        if (! Cache::add($key, 1, now()->endOfDay())) {
            return;
        }

        app()->terminating(function () use ($church, $key) {
            try {
                $this->processChurch($church);
            } catch (\Throwable $e) {
                Cache::forget($key);
                Log::error('Missed Sunday SMS failed', [
                    'church_id' => $church->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    /**
     * Send as soon as a finished Sunday roll is saved, without waiting for the next day.
     */
    public function remindForFinishedSunday(Church $church, ChurchService $service): void
    {
        if ($service->service_type !== ChurchServiceType::Sunday || ! $this->sundayServiceHasFinished($service)) {
            return;
        }

        try {
            $this->processChurch($church);
        } catch (\Throwable $e) {
            Log::error('Missed Sunday SMS failed after attendance save', [
                'church_id' => $church->id,
                'service_id' => $service->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return array{notified: int, skipped: int, reason: ?string}
     */
    public function processChurch(Church $church): array
    {
        if (! (bool) $this->churchSettings->get($church, 'missed_attendance_sms', true)) {
            return ['notified' => 0, 'skipped' => 0, 'reason' => null];
        }

        if (! $this->sms->churchSmsEnabled($church)) {
            return [
                'notified' => 0,
                'skipped' => 0,
                'reason' => 'SMS is off for this church, or the package does not include SMS.',
            ];
        }

        $windows = $this->attendanceService
            ->sundayAbsenceWindows($church, self::MISS_THRESHOLD)
            ->filter(fn (array $window) => count($window['dates']) >= self::MISS_THRESHOLD)
            ->values();

        if ($windows->isEmpty()) {
            return [
                'notified' => 0,
                'skipped' => 0,
                'reason' => 'Fewer than 3 recorded Sunday dates for every branch.',
            ];
        }

        $notified = 0;
        $skipped = 0;

        foreach ($windows as $window) {
            $result = $this->processWindow($church, $window);
            $notified += $result['notified'];
            $skipped += $result['skipped'];
        }

        return ['notified' => $notified, 'skipped' => $skipped, 'reason' => null];
    }

    /**
     * @param  array{branch_id: ?int, dates: list<string>, services: \Illuminate\Support\Collection<int, ChurchService>, through: ChurchService}  $window
     * @return array{notified: int, skipped: int}
     */
    private function processWindow(Church $church, array $window): array
    {
        /** @var ChurchService $throughService */
        $throughService = $window['through'];
        $serviceIds = $window['services']->pluck('id')->all();

        $attended = AttendanceRecord::forChurch($church->id)
            ->membersOnly()
            ->where('source_type', AttendanceSourceType::ChurchService->value)
            ->whereIn('source_id', $serviceIds)
            ->get(['member_id', 'source_id'])
            ->groupBy('member_id');

        $alreadyNotified = AttendanceAbsenceNotification::forChurch($church->id)
            ->where('through_service_id', $throughService->id)
            ->pluck('member_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $members = Member::forChurch($church->id)
            ->activeMembers()
            ->whereNotNull('phone_number')
            ->where('phone_number', '!=', '')
            ->when(
                $window['branch_id'],
                fn ($query) => $query->where('branch_id', $window['branch_id']),
                fn ($query) => $query->whereNull('branch_id'),
            )
            ->whereNotIn('id', $alreadyNotified)
            ->get();

        $notified = 0;
        $skipped = 0;

        foreach ($members as $member) {
            $presentIds = $attended->get($member->id)?->pluck('source_id')->map(fn ($id) => (int) $id)->all() ?? [];
            $consecutive = $this->attendanceService->consecutiveSundayDateMisses(
                $window['dates'],
                $window['services'],
                $presentIds,
            );

            if ($consecutive < self::MISS_THRESHOLD) {
                continue;
            }

            if ($this->hasOpenAbsenceReminder($church, $member, $throughService)) {
                $skipped++;

                continue;
            }

            $datesForMessage = $window['services']
                ->unique(fn (ChurchService $service) => $service->service_date?->toDateString())
                ->values();

            $sent = $this->sms->sendMissedSundayReminder(
                $church,
                $member,
                self::MISS_THRESHOLD,
                $datesForMessage,
            );

            if (! ($sent['ok'] ?? false)) {
                $skipped++;

                continue;
            }

            try {
                AttendanceAbsenceNotification::create([
                    'church_id' => $church->id,
                    'member_id' => $member->id,
                    'through_service_id' => $throughService->id,
                    'miss_count' => self::MISS_THRESHOLD,
                    'sent_at' => now(),
                ]);
            } catch (QueryException) {
                $skipped++;

                continue;
            }

            $notified++;
        }

        return ['notified' => $notified, 'skipped' => $skipped];
    }

    /**
     * Mute further reminders until the member attends a later recorded Sunday in the same branch.
     */
    private function hasOpenAbsenceReminder(Church $church, Member $member, ChurchService $throughService): bool
    {
        $last = AttendanceAbsenceNotification::forChurch($church->id)
            ->where('member_id', $member->id)
            ->latest('sent_at')
            ->first();

        if (! $last) {
            return false;
        }

        $lastService = ChurchService::withTrashed()->find($last->through_service_id);
        $lastDate = $lastService?->service_date;

        if (! $lastDate) {
            return false;
        }

        $attendedAfter = AttendanceRecord::forChurch($church->id)
            ->membersOnly()
            ->where('member_id', $member->id)
            ->where('source_type', AttendanceSourceType::ChurchService->value)
            ->whereIn('source_id', function ($query) use ($church, $lastDate, $lastService) {
                $query->select('id')
                    ->from('church_services')
                    ->where('church_id', $church->id)
                    ->where('service_type', ChurchServiceType::Sunday->value)
                    ->whereDate('service_date', '>', $lastDate->toDateString())
                    ->whereNull('deleted_at');

                if ($lastService->branch_id) {
                    $query->where('branch_id', $lastService->branch_id);
                } else {
                    $query->whereNull('branch_id');
                }
            })
            ->exists();

        return ! $attendedAfter;
    }

    private function sundayServiceHasFinished(ChurchService $service): bool
    {
        if ($service->status === ChurchServiceStatus::Completed) {
            return true;
        }

        $endsAt = $service->attendanceEndsAt()
            ?? $service->attendanceOpensAt()?->copy()->endOfDay();

        return $endsAt !== null && $service->nowInChurch()->greaterThanOrEqualTo($endsAt);
    }
}
