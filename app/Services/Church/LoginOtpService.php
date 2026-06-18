<?php

namespace App\Services\Church;

use App\Models\Church;
use App\Models\LoginOtp;
use App\Models\User;
use App\Services\Sms\ChurchSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginOtpService
{
    private const MAX_ATTEMPTS = 5;

    private const OTP_TTL_MINUTES = 5;

    private const MAX_RESEND_ATTEMPTS = 3;

    public function __construct(
        private readonly ChurchSmsService $churchSmsService,
        private readonly ChurchSettingsService $churchSettings,
    ) {}

    public function isEnabledForChurch(Church $church): bool
    {
        return $this->churchSmsService->platformSmsEnabled()
            && $church->hasPackageFeature('sms')
            && (bool) $this->churchSettings->get($church, 'otp_login_enabled', false);
    }

    public function issue(User $user, Church $church, string $loginIdentifier, Request $request): ?LoginOtp
    {
        $phone = $user->loginPhone();

        if ($phone === null || $phone === '') {
            return null;
        }

        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        LoginOtp::query()
            ->where('user_id', $user->id)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->update(['is_used' => true, 'used_at' => now()]);

        $otp = LoginOtp::create([
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

        $sent = $this->churchSmsService->sendLoginOtp($church, $user, $otpCode);

        if (! ($sent['ok'] ?? false)) {
            Log::warning('Login OTP SMS failed', [
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
        $otp = LoginOtp::query()
            ->where('user_id', $userId)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $otp) {
            return ['success' => false, 'message' => 'No valid OTP found. Please sign in again to receive a new code.'];
        }

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            return ['success' => false, 'message' => 'Too many failed attempts. Please sign in again.'];
        }

        if ($otp->otp_code !== $otpCode) {
            $otp->incrementAttempts();
            $remaining = self::MAX_ATTEMPTS - $otp->attempts;

            if ($remaining <= 0) {
                return ['success' => false, 'message' => 'Invalid OTP. Maximum attempts reached. Please sign in again.'];
            }

            return ['success' => false, 'message' => "Invalid OTP. You have {$remaining} attempt(s) remaining."];
        }

        $otp->markAsUsed();

        return ['success' => true, 'user' => $otp->user];
    }

    public function resend(User $user, Church $church, string $loginIdentifier, Request $request, int $resendAttempts): ?LoginOtp
    {
        if ($resendAttempts >= self::MAX_RESEND_ATTEMPTS) {
            return null;
        }

        return $this->issue($user, $church, $loginIdentifier, $request);
    }

    public function maxResendAttempts(): int
    {
        return self::MAX_RESEND_ATTEMPTS;
    }

    public function activeOtpForUser(int $userId): ?LoginOtp
    {
        return LoginOtp::query()
            ->where('user_id', $userId)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }
}
