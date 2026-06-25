<?php

namespace App\Services\Church;

use App\Enums\UserStatus;
use App\Models\Church;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Services\Owner\AuditLogService;
use App\Services\Sms\ChurchSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PasswordResetService
{
    private const MAX_ATTEMPTS = 5;

    private const OTP_TTL_MINUTES = 10;

    private const MAX_RESEND_ATTEMPTS = 3;

    public function __construct(
        private readonly ChurchSmsService $churchSmsService,
        private readonly ChurchSettingsService $churchSettings,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function canSendOtp(Church $church): bool
    {
        return $this->churchSmsService->platformSmsEnabled()
            && $church->hasPackageFeature('sms')
            && (bool) $this->churchSettings->get($church, 'password_reset_sms', true);
    }

    /**
     * @return array{success: bool, message?: string, user?: User, church?: Church}
     */
    public function resolveUser(string $identifier): array
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return ['success' => false, 'message' => 'No account found with that email or member ID.'];
        }

        $user = User::findByLoginIdentifier($identifier);

        if (! $user) {
            return ['success' => false, 'message' => 'No account found with that email or member ID.'];
        }

        if ($user->status !== UserStatus::Active) {
            return ['success' => false, 'message' => 'Your account is not active. Contact your church administrator.'];
        }

        if (! $user->isChurchPortalUser()) {
            return ['success' => false, 'message' => 'This reset is for church members and staff only.'];
        }

        $church = $user->church;

        if (! $church) {
            return ['success' => false, 'message' => 'Your account is not linked to a church.'];
        }

        if ($church->status->value === 'suspended') {
            return ['success' => false, 'message' => 'This church has been suspended. Contact platform support.'];
        }

        if (empty($user->loginPhone())) {
            return ['success' => false, 'message' => 'No phone number is on file for your account. Contact your church administrator.'];
        }

        if (! $this->canSendOtp($church)) {
            return ['success' => false, 'message' => 'Password reset via SMS is not available for your church. Contact your church administrator.'];
        }

        return ['success' => true, 'user' => $user, 'church' => $church];
    }

    public function issue(User $user, Church $church, string $loginIdentifier, Request $request): ?PasswordResetOtp
    {
        $phone = $user->loginPhone();

        if ($phone === null || $phone === '') {
            return null;
        }

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetOtp::query()
            ->where('user_id', $user->id)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->update(['is_used' => true, 'used_at' => now()]);

        $otp = PasswordResetOtp::create([
            'user_id' => $user->id,
            'church_id' => $church->id,
            'otp_code' => $otpCode,
            'login_identifier' => $loginIdentifier,
            'phone' => $phone,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'is_used' => false,
            'attempts' => 0,
        ]);

        $sent = $this->churchSmsService->sendPasswordResetOtp($church, $user, $otpCode);

        if (! ($sent['ok'] ?? false)) {
            Log::warning('Password reset OTP SMS failed', [
                'user_id' => $user->id,
                'reason' => $sent['reason'] ?? 'unknown',
            ]);
        }

        return $otp;
    }

    /**
     * @return array{success: bool, message?: string, user?: User}
     */
    public function verify(string $otpCode, int $userId): array
    {
        $otp = PasswordResetOtp::query()
            ->where('user_id', $userId)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp) {
            return ['success' => false, 'message' => 'No valid verification code found. Please request a new one.'];
        }

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            return ['success' => false, 'message' => 'Too many failed attempts. Please request a new code.'];
        }

        if ($otp->otp_code !== $otpCode) {
            $otp->incrementAttempts();
            $remaining = self::MAX_ATTEMPTS - $otp->attempts;

            if ($remaining <= 0) {
                return ['success' => false, 'message' => 'Invalid code. Maximum attempts reached. Please request a new code.'];
            }

            return ['success' => false, 'message' => "Invalid code. You have {$remaining} attempt(s) remaining."];
        }

        $otp->markAsUsed();

        return ['success' => true, 'user' => $otp->user];
    }

    public function resend(User $user, Church $church, string $loginIdentifier, Request $request, int $resendAttempts): ?PasswordResetOtp
    {
        if ($resendAttempts >= self::MAX_RESEND_ATTEMPTS) {
            return null;
        }

        return $this->issue($user, $church, $loginIdentifier, $request);
    }

    public function resetPassword(User $user, string $newPassword): void
    {
        $user->update(['password' => $newPassword]);

        $this->auditLogService->log('church.user.password_reset_self', $user, null, [
            'email' => $user->email,
        ], $user->church_id);
    }

    public function maxResendAttempts(): int
    {
        return self::MAX_RESEND_ATTEMPTS;
    }

    public function activeOtpForUser(int $userId): ?PasswordResetOtp
    {
        return PasswordResetOtp::query()
            ->where('user_id', $userId)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }
}
