<?php

namespace App\Services\Owner;

use App\Enums\ChurchStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Church;
use App\Services\Sms\ChurchSmsService;
use App\Services\Sms\SmsGatewayService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TrialExpiryService
{
    public const REMINDER_DAYS_BEFORE = 4;

    public function __construct(
        private readonly SmsGatewayService $smsGateway,
        private readonly ChurchSmsService $churchSmsService,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @return array{reminded: int, expired: int, expiry_notified: int}
     */
    public function processDue(): array
    {
        return [
            'reminded' => $this->sendUpcomingExpiryReminders(),
            'expired' => ($expired = $this->expireEndedTrials())['expired'],
            'expiry_notified' => $expired['notified'],
        ];
    }

    public function sendUpcomingExpiryReminders(): int
    {
        $targetDate = now()->addDays(self::REMINDER_DAYS_BEFORE)->toDateString();
        $sent = 0;

        $this->trialChurches()
            ->filter(function (Church $church) use ($targetDate) {
                $endsAt = $this->trialEndsAt($church);

                return $endsAt
                    && $endsAt->isFuture()
                    && $endsAt->toDateString() === $targetDate
                    && ! $this->reminderAlreadySent($church, $targetDate);
            })
            ->each(function (Church $church) use (&$sent, $targetDate) {
                $endsAt = $this->trialEndsAt($church);
                $message = implode(' ', [
                    config('app.name').':',
                    'Your free trial for '.$church->name.' expires in '.self::REMINDER_DAYS_BEFORE.' days',
                    '('.$endsAt->format('M d, Y').').',
                    'Pay your subscription to continue without interruption.',
                    'Contact support to upgrade.',
                ]);

                if ($this->notifyChurchContacts($church, $message, 'trial_reminder')) {
                    $this->markReminderSent($church, $targetDate);
                    $sent++;
                }
            });

        return $sent;
    }

    /**
     * @return array{expired: int, notified: int}
     */
    public function expireEndedTrials(): array
    {
        $expired = 0;
        $notified = 0;

        $this->trialChurches()
            ->filter(function (Church $church) {
                $endsAt = $this->trialEndsAt($church);

                return $endsAt && $endsAt->isPast();
            })
            ->each(function (Church $church) use (&$expired, &$notified) {
                $result = $this->expireChurch($church, notify: true);
                if ($result['expired']) {
                    $expired++;
                }
                if ($result['notified']) {
                    $notified++;
                }
            });

        return compact('expired', 'notified');
    }

    /**
     * Expire a single church if its trial has ended.
     *
     * @return array{expired: bool, notified: bool}
     */
    public function expireChurchIfDue(Church $church, bool $notify = true): array
    {
        if ($church->status !== ChurchStatus::Trial) {
            return ['expired' => false, 'notified' => false];
        }

        $endsAt = $this->trialEndsAt($church);

        if (! $endsAt || $endsAt->isFuture()) {
            return ['expired' => false, 'notified' => false];
        }

        return $this->expireChurch($church, $notify);
    }

    /**
     * @return array{expired: bool, notified: bool}
     */
    public function expireChurch(Church $church, bool $notify = true): array
    {
        if ($church->status === ChurchStatus::Expired) {
            $notified = false;
            if ($notify && ! $this->expiryAlreadyNotified($church)) {
                $notified = $this->sendExpiredSms($church);
            }

            return ['expired' => false, 'notified' => $notified];
        }

        $church->update([
            'status' => ChurchStatus::Expired,
        ]);

        $church->subscriptions()
            ->whereIn('status', [SubscriptionStatus::Trial->value, SubscriptionStatus::Active->value])
            ->update([
                'status' => SubscriptionStatus::Expired->value,
                'ends_at' => now(),
            ]);

        $this->auditLogService->log(
            'church.trial_expired',
            $church,
            ['status' => ChurchStatus::Trial->value],
            ['status' => ChurchStatus::Expired->value],
            $church->id
        );

        $notified = false;
        if ($notify) {
            $notified = $this->sendExpiredSms($church);
        }

        return ['expired' => true, 'notified' => $notified];
    }

    public function trialEndsAt(Church $church): ?\Illuminate\Support\Carbon
    {
        if ($church->trial_ends_at) {
            return $church->trial_ends_at;
        }

        $church->loadMissing('activeSubscription');

        return $church->activeSubscription?->trial_ends_at
            ?? $church->activeSubscription?->ends_at;
    }

    /**
     * @return Collection<int, Church>
     */
    private function trialChurches(): Collection
    {
        return Church::query()
            ->with(['adminUser', 'activeSubscription'])
            ->where('status', ChurchStatus::Trial)
            ->get();
    }

    private function sendExpiredSms(Church $church): bool
    {
        if ($this->expiryAlreadyNotified($church)) {
            return false;
        }

        $message = implode(' ', [
            config('app.name').':',
            'Your free trial for '.$church->name.' has expired.',
            'The system is locked.',
            'Please pay your subscription to continue using the platform.',
            'Contact support to activate your plan.',
        ]);

        $sent = $this->notifyChurchContacts($church, $message, 'trial_expired');

        if ($sent) {
            $this->markExpiryNotified($church);
        }

        return $sent;
    }

    private function notifyChurchContacts(Church $church, string $message, string $context): bool
    {
        $phones = collect([
            $church->adminUser?->phone,
            $church->phone,
        ])
            ->map(fn ($phone) => $this->smsGateway->normalizePhone($phone))
            ->filter()
            ->unique()
            ->values();

        if ($phones->isEmpty()) {
            Log::info('Trial SMS skipped: no phone numbers', [
                'church_id' => $church->id,
                'context' => $context,
            ]);

            return false;
        }

        if (! $this->churchSmsService->platformSmsEnabled()) {
            Log::info('Trial SMS skipped: platform SMS not enabled', [
                'church_id' => $church->id,
                'context' => $context,
            ]);

            return false;
        }

        $anySent = false;

        foreach ($phones as $phone) {
            try {
                $result = $this->smsGateway->send($phone, $message);
                if ($result['ok'] ?? false) {
                    $anySent = true;
                } else {
                    Log::warning('Trial SMS not delivered', [
                        'church_id' => $church->id,
                        'context' => $context,
                        'to' => $phone,
                        'reason' => $result['reason'] ?? $result['body'] ?? 'unknown',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Trial SMS failed: '.$e->getMessage(), [
                    'church_id' => $church->id,
                    'context' => $context,
                ]);
            }
        }

        return $anySent;
    }

    private function reminderAlreadySent(Church $church, string $targetDate): bool
    {
        $settings = $church->settings ?? [];

        return ($settings['trial_reminder_sent_on'] ?? null) === $targetDate;
    }

    private function markReminderSent(Church $church, string $targetDate): void
    {
        $settings = $church->settings ?? [];
        $settings['trial_reminder_sent_on'] = $targetDate;
        $church->update(['settings' => $settings]);
    }

    private function expiryAlreadyNotified(Church $church): bool
    {
        $settings = $church->settings ?? [];

        return ! empty($settings['trial_expired_notified_at']);
    }

    private function markExpiryNotified(Church $church): void
    {
        $settings = $church->settings ?? [];
        $settings['trial_expired_notified_at'] = now()->toIso8601String();
        $church->update(['settings' => $settings]);
    }
}
