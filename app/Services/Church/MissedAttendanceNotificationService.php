<?php

namespace App\Services\Church;

use App\Enums\AttendanceSourceType;
use App\Models\AttendanceAbsenceNotification;
use App\Models\AttendanceRecord;
use App\Models\Church;
use App\Models\ChurchService;
use App\Models\Member;
use App\Services\Sms\ChurchSmsService;

class MissedAttendanceNotificationService
{
    public const MISS_THRESHOLD = 3;

    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly ChurchSmsService $sms,
        private readonly ChurchSettingsService $churchSettings,
    ) {}

    /**
     * @return array{notified: int, skipped: int}
     */
    public function processChurch(Church $church): array
    {
        if (! (bool) $this->churchSettings->get($church, 'missed_attendance_sms', true)) {
            return ['notified' => 0, 'skipped' => 0];
        }

        if (! $this->sms->churchSmsEnabled($church)) {
            return ['notified' => 0, 'skipped' => 0];
        }

        $sundays = $this->attendanceService->recordedSundayServices($church, self::MISS_THRESHOLD)->values();

        if ($sundays->count() < self::MISS_THRESHOLD) {
            return ['notified' => 0, 'skipped' => 0];
        }

        $window = $sundays->take(self::MISS_THRESHOLD)->values();
        /** @var ChurchService $throughService */
        $throughService = $window->first();
        $serviceIds = $window->pluck('id')->all();

        $attendedMemberIds = AttendanceRecord::forChurch($church->id)
            ->membersOnly()
            ->where('source_type', AttendanceSourceType::ChurchService->value)
            ->whereIn('source_id', $serviceIds)
            ->pluck('member_id')
            ->unique()
            ->all();

        $alreadyNotified = AttendanceAbsenceNotification::forChurch($church->id)
            ->where('through_service_id', $throughService->id)
            ->pluck('member_id')
            ->all();

        $members = Member::forChurch($church->id)
            ->activeMembers()
            ->whereNotNull('phone_number')
            ->where('phone_number', '!=', '')
            ->whereNotIn('id', $attendedMemberIds)
            ->whereNotIn('id', $alreadyNotified)
            ->get();

        $notified = 0;
        $skipped = 0;

        foreach ($members as $member) {
            if ($this->hasOpenAbsenceReminder($church, $member, $throughService)) {
                $skipped++;

                continue;
            }

            $sent = $this->sms->sendMissedSundayReminder(
                $church,
                $member,
                self::MISS_THRESHOLD,
                $window
            );

            if (! ($sent['ok'] ?? false)) {
                $skipped++;

                continue;
            }

            AttendanceAbsenceNotification::create([
                'church_id' => $church->id,
                'member_id' => $member->id,
                'through_service_id' => $throughService->id,
                'miss_count' => self::MISS_THRESHOLD,
                'sent_at' => now(),
            ]);

            $notified++;
        }

        return ['notified' => $notified, 'skipped' => $skipped];
    }

    /**
     * Mute further reminders until the member attends a later recorded Sunday.
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

        $last->loadMissing('throughService');
        $lastDate = $last->throughService?->service_date;

        if (! $lastDate) {
            return $last->through_service_id !== $throughService->id;
        }

        $attendedAfter = AttendanceRecord::forChurch($church->id)
            ->membersOnly()
            ->where('member_id', $member->id)
            ->where('source_type', AttendanceSourceType::ChurchService->value)
            ->whereIn('source_id', function ($query) use ($church, $lastDate) {
                $query->select('id')
                    ->from('church_services')
                    ->where('church_id', $church->id)
                    ->where('service_type', \App\Enums\ChurchServiceType::Sunday->value)
                    ->whereDate('service_date', '>', $lastDate->toDateString());
            })
            ->exists();

        return ! $attendedAfter;
    }
}
