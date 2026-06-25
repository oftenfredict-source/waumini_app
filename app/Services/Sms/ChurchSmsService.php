<?php

namespace App\Services\Sms;

use App\Enums\AnnouncementTargetType;
use App\Models\Announcement;
use App\Models\Church;
use App\Models\Member;
use App\Models\SmsLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Church\ChurchSettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ChurchSmsService
{
    public function __construct(
        private readonly SmsGatewayService $gateway,
        private readonly ChurchSettingsService $churchSettings,
        private readonly SmsTemplateService $templates,
    ) {}

    public function platformSmsEnabled(): bool
    {
        return (bool) SystemSetting::getValue('notifications', 'sms_enabled', false)
            && $this->gateway->isConfigured();
    }

    public function churchSmsEnabled(Church $church): bool
    {
        return $this->canSendTransactional($church)
            && (bool) $this->churchSettings->get($church, 'sms_enabled', false);
    }

    public function canSendTransactional(Church $church): bool
    {
        return $this->platformSmsEnabled()
            && $church->hasPackageFeature('sms');
    }

    /**
     * @return array{ok: bool, reason?: string, body?: string}
     */
    public function sendTransactionalForChurch(Church $church, string $phone, string $message, string $context = 'general'): array
    {
        if (! $this->canSendTransactional($church)) {
            Log::info('Transactional SMS skipped', ['church_id' => $church->id, 'context' => $context, 'reason' => 'not_allowed']);

            return ['ok' => false, 'reason' => 'not_allowed'];
        }

        if (! $this->withinMonthlyLimit($church)) {
            $this->log($church, $phone, $message, $context, 'skipped', 'Monthly SMS limit reached');

            return ['ok' => false, 'reason' => 'limit_reached'];
        }

        $result = $this->gateway->send($phone, $message);

        $this->log(
            $church,
            $phone,
            $message,
            $context,
            ($result['ok'] ?? false) ? 'sent' : 'failed',
            $result['body'] ?? ($result['reason'] ?? null),
        );

        return $result;
    }

    /**
     * @return array{ok: bool, reason?: string, body?: string}
     */
    public function sendForChurch(Church $church, string $phone, string $message, string $context = 'general'): array
    {
        if (! $this->churchSmsEnabled($church)) {
            return ['ok' => false, 'reason' => 'disabled'];
        }

        if (! $this->withinMonthlyLimit($church)) {
            $this->log($church, $phone, $message, $context, 'skipped', 'Monthly SMS limit reached');

            return ['ok' => false, 'reason' => 'limit_reached'];
        }

        $result = $this->gateway->send($phone, $message);

        $this->log(
            $church,
            $phone,
            $message,
            $context,
            ($result['ok'] ?? false) ? 'sent' : 'failed',
            $result['body'] ?? ($result['reason'] ?? null),
        );

        return $result;
    }

    public function sendTest(string $phone, string $message): array
    {
        if (! $this->gateway->isConfigured()) {
            return ['ok' => false, 'reason' => 'config_missing'];
        }

        return $this->gateway->send($phone, $message, debug: true);
    }

    public function sendLeaderAppointment(Church $church, Member $member, string $position): void
    {
        if (! $this->churchSmsEnabled($church) || empty($member->phone_number)) {
            return;
        }

        $message = $this->templates->render($church, 'leader_appointment', [
            '{{name}}' => $member->full_name,
            '{{position}}' => $position,
            '{{church_name}}' => $church->name,
        ]);

        $this->sendForChurch($church, $member->phone_number, $message, 'leader_appointment');
    }

    public function sendMemberCredentials(Church $church, Member $member, string $password): void
    {
        if (! $this->churchSmsEnabled($church) || empty($member->phone_number)) {
            return;
        }

        if (! (bool) $this->churchSettings->get($church, 'member_credentials_sms', false)) {
            return;
        }

        $message = $this->templates->render($church, 'member_credentials', [
            '{{name}}' => $member->full_name,
            '{{church_name}}' => $church->name,
            '{{member_id}}' => $member->member_number,
            '{{password}}' => $password,
        ]);

        $this->sendForChurch($church, $member->phone_number, $message, 'member_credentials');
    }

    public function sendPasswordReset(Church $church, User $user, string $password): array
    {
        if (! (bool) $this->churchSettings->get($church, 'password_reset_sms', true)) {
            return ['ok' => false, 'reason' => 'setting_disabled'];
        }

        $user->loadMissing('member');
        $phone = $user->loginPhone();

        if ($phone === null || $phone === '') {
            Log::info('Password reset SMS skipped: no phone', ['user_id' => $user->id]);

            return ['ok' => false, 'reason' => 'no_phone'];
        }

        $loginId = $user->member?->member_number ?? $user->email;

        $message = $this->templates->render($church, 'password_reset', [
            '{{name}}' => $user->name,
            '{{church_name}}' => $church->name,
            '{{login_id}}' => $loginId,
            '{{password}}' => $password,
        ]);

        return $this->sendTransactionalForChurch($church, $phone, $message, 'password_reset');
    }

    public function sendLoginOtp(Church $church, User $user, string $otpCode): array
    {
        $phone = $user->loginPhone();

        if ($phone === null || $phone === '') {
            return ['ok' => false, 'reason' => 'no_phone'];
        }

        if (! $this->platformSmsEnabled() || ! $church->hasPackageFeature('sms')) {
            return ['ok' => false, 'reason' => 'disabled'];
        }

        $message = $this->templates->render($church, 'login_otp', [
            '{{name}}' => $user->name,
            '{{church_name}}' => $church->name,
            '{{otp_code}}' => $otpCode,
        ]);

        return $this->sendTransactionalForChurch($church, $phone, $message, 'login_otp');
    }

    public function sendPasswordResetOtp(Church $church, User $user, string $otpCode): array
    {
        if (! (bool) $this->churchSettings->get($church, 'password_reset_sms', true)) {
            return ['ok' => false, 'reason' => 'setting_disabled'];
        }

        $phone = $user->loginPhone();

        if ($phone === null || $phone === '') {
            return ['ok' => false, 'reason' => 'no_phone'];
        }

        if (! $this->platformSmsEnabled() || ! $church->hasPackageFeature('sms')) {
            return ['ok' => false, 'reason' => 'disabled'];
        }

        $message = $this->templates->render($church, 'password_reset_otp', [
            '{{name}}' => $user->name,
            '{{church_name}}' => $church->name,
            '{{otp_code}}' => $otpCode,
        ]);

        return $this->sendTransactionalForChurch($church, $phone, $message, 'password_reset_otp');
    }

    /**
     * @return array{ok: bool, reason?: string, body?: string}
     */
    public function sendPromiseGuestInvitation(
        Church $church,
        string $phone,
        string $guestName,
        ?\Carbon\CarbonInterface $promisedDate,
        ?\App\Models\ChurchService $service = null,
        ?\App\Models\SpecialEvent $event = null,
        bool $welcomeBack = false,
    ): array {
        if ($phone === '') {
            return ['ok' => false, 'reason' => 'no_phone'];
        }

        $message = $this->buildPromiseGuestMessage($church, $guestName, $promisedDate, $service, $event, $welcomeBack);

        if ($this->churchSmsEnabled($church)) {
            return $this->sendForChurch($church, $phone, $message, 'promise_guest');
        }

        return $this->sendTransactionalForChurch($church, $phone, $message, 'promise_guest');
    }

    private function buildPromiseGuestMessage(
        Church $church,
        string $guestName,
        ?\Carbon\CarbonInterface $promisedDate,
        ?\App\Models\ChurchService $service,
        ?\App\Models\SpecialEvent $event,
        bool $welcomeBack = false,
    ): string {
        if ($welcomeBack && ! $service && ! $event) {
            return $this->templates->render($church, 'promise_guest_welcome_back_generic', [
                '{{name}}' => $guestName,
                '{{church_name}}' => $church->name,
            ]);
        }

        $date = $promisedDate?->format('d/m/Y') ?? '';
        $eventName = $service?->displayTitle()
            ?? $event?->title
            ?? 'ibada';

        $details = [];
        if ($service?->start_time) {
            $details[] = 'Muda: '.substr((string) $service->start_time, 0, 5);
        }
        if ($service?->venue || $event?->venue) {
            $details[] = 'Mahali: '.($service?->venue ?? $event?->venue);
        }
        if ($service?->preacher || $event?->speaker) {
            $details[] = 'Mhudumu: '.($service?->preacher ?? $event?->speaker);
        }

        $detailText = $details !== [] ? "\n".implode("\n", $details) : '';
        $templateKey = $welcomeBack ? 'promise_guest_welcome_back' : 'promise_guest_reminder';

        return $this->templates->render($church, $templateKey, [
            '{{name}}' => $guestName,
            '{{church_name}}' => $church->name,
            '{{event_name}}' => $eventName,
            '{{date}}' => $date,
            '{{details}}' => $detailText,
        ]);
    }

    public function sendFinanceApprovalForRecord(Church $church, string $type, Model $record): void
    {
        if (! in_array($type, ['tithe', 'offering', 'pledge_payment'], true)) {
            return;
        }

        if (! $this->churchSmsEnabled($church)) {
            return;
        }

        if (! (bool) $this->churchSettings->get($church, 'finance_approval_sms', true)) {
            return;
        }

        $member = match ($type) {
            'pledge_payment' => $record->pledge?->member,
            default => $record->member,
        };

        if (! $member || empty($member->phone_number)) {
            return;
        }

        $paymentType = match ($type) {
            'tithe' => 'Zaka',
            'offering' => 'Sadaka',
            'pledge_payment' => 'Ahadi',
            default => 'Malipo',
        };

        $amount = (float) $record->amount;
        $dateField = match ($type) {
            'tithe' => 'tithe_date',
            'offering' => 'offering_date',
            'pledge_payment' => 'payment_date',
            default => null,
        };

        $date = $dateField && $record->{$dateField}
            ? $record->{$dateField}->format('d/m/Y')
            : now()->format('d/m/Y');

        $formattedAmount = number_format($amount, 0);

        $message = $this->templates->render($church, 'finance_approval', [
            '{{name}}' => $member->full_name,
            '{{payment_type}}' => $paymentType,
            '{{amount}}' => $formattedAmount,
            '{{date}}' => $date,
        ]);

        $this->sendForChurch($church, $member->phone_number, $message, 'finance_approval');
    }

    public function sendAnnouncement(Church $church, Announcement $announcement): int
    {
        if (! $this->churchSmsEnabled($church)) {
            return 0;
        }

        if (! (bool) $this->churchSettings->get($church, 'announcement_sms', false)) {
            return 0;
        }

        $recipients = $this->announcementRecipients($church, $announcement);

        if ($recipients->isEmpty()) {
            return 0;
        }

        $message = $this->buildAnnouncementMessage($church, $announcement);
        $sent = 0;

        foreach ($recipients as $member) {
            $result = $this->sendForChurch($church, (string) $member->phone_number, $message, 'announcement');

            if ($result['ok'] ?? false) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * @return Collection<int, Member>
     */
    private function announcementRecipients(Church $church, Announcement $announcement): Collection
    {
        $query = Member::forChurch($church->id)
            ->where('status', 'active')
            ->whereNotNull('phone_number')
            ->where('phone_number', '!=', '');

        return match ($announcement->target_type) {
            AnnouncementTargetType::All => $query->get(),
            AnnouncementTargetType::Specific => $announcement->targetedMembers()
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->get(),
            AnnouncementTargetType::Department => $announcement->department
                ? $announcement->department->members()
                    ->whereNotNull('phone_number')
                    ->where('phone_number', '!=', '')
                    ->get()
                : collect(),
        };
    }

    private function buildAnnouncementMessage(Church $church, Announcement $announcement): string
    {
        $content = trim(strip_tags((string) $announcement->content));
        $content = preg_replace('/\s+/', ' ', $content) ?? $content;

        if (strlen($content) > 120) {
            $content = substr($content, 0, 117).'...';
        }

        return $this->templates->render($church, 'announcement', [
            '{{church_name}}' => $church->name,
            '{{title}}' => $announcement->title,
            '{{content}}' => $content,
        ]);
    }

    /**
     * @return array{ok: bool, reason?: string, body?: string}
     */
    public function sendManualMessage(Church $church, string $phone, string $message, string $context = 'manual'): array
    {
        if ($this->churchSmsEnabled($church)) {
            return $this->sendForChurch($church, $phone, $message, $context);
        }

        return $this->sendTransactionalForChurch($church, $phone, $message, $context);
    }

    /**
     * @return array{ok: bool, reason?: string, body?: string}
     */
    public function resendLoggedMessage(Church $church, SmsLog $log): array
    {
        return $this->sendManualMessage($church, $log->recipient, $log->message, $log->context);
    }

    private function withinMonthlyLimit(Church $church): bool
    {
        $package = $church->activeSubscription?->package;
        $limit = $package?->max_sms_monthly;

        if ($limit === null || $limit <= 0) {
            return true;
        }

        return SmsLog::monthlyCountForChurch($church->id) < $limit;
    }

    private function log(
        Church $church,
        string $phone,
        string $message,
        string $context,
        string $status,
        ?string $providerResponse = null,
    ): void {
        try {
            SmsLog::create([
                'church_id' => $church->id,
                'recipient' => $this->gateway->normalizePhone($phone),
                'context' => $context,
                'message' => $message,
                'status' => $status,
                'provider_response' => $providerResponse,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to write SMS log: '.$e->getMessage());
        }
    }
}
