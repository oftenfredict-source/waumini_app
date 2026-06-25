<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Http\Requests\Church\CompletePasswordResetRequest;
use App\Models\User;
use App\Services\Church\PasswordResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly PasswordResetService $passwordResetService,
    ) {}

    public function showRequestForm(): View
    {
        return view('church.auth.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string'],
        ]);

        $identifier = trim($validated['email']);
        $result = $this->passwordResetService->resolveUser($identifier);

        if (! ($result['success'] ?? false)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', $result['message'] ?? 'Unable to process your request.');
        }

        /** @var User $user */
        $user = $result['user'];
        /** @var \App\Models\Church $church */
        $church = $result['church'];

        $otp = $this->passwordResetService->issue($user, $church, $identifier, $request);

        if (! $otp) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Unable to send verification code. Please try again or contact your church administrator.');
        }

        $request->session()->put([
            'password_reset_user_id' => $user->id,
            'password_reset_login_identifier' => $identifier,
            'password_reset_resend_attempts' => 0,
            'password_reset_verified' => false,
        ]);

        return redirect()
            ->route('church.password.forgot.verify')
            ->with('info', 'A verification code has been sent to your phone. Enter it below to reset your password.');
    }

    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_user_id')) {
            return redirect()
                ->route('church.password.forgot')
                ->with('error', 'Please enter your email or member ID first.');
        }

        $userId = (int) $request->session()->get('password_reset_user_id');
        $otp = $this->passwordResetService->activeOtpForUser($userId);

        return view('church.auth.forgot-password-verify', [
            'loginIdentifier' => $request->session()->get('password_reset_login_identifier'),
            'otpExpiresAt' => $otp?->expires_at,
            'resendAttempts' => (int) $request->session()->get('password_reset_resend_attempts', 0),
            'maxResendAttempts' => $this->passwordResetService->maxResendAttempts(),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        if (! $request->session()->has('password_reset_user_id')) {
            return redirect()
                ->route('church.password.forgot')
                ->with('error', 'Session expired. Please start again.');
        }

        $userId = (int) $request->session()->get('password_reset_user_id');
        $result = $this->passwordResetService->verify($request->input('otp'), $userId);

        if (! ($result['success'] ?? false)) {
            return back()->with('error', $result['message'] ?? 'Invalid verification code.');
        }

        $request->session()->put('password_reset_verified', true);

        return redirect()
            ->route('church.password.forgot.reset')
            ->with('success', 'Code verified. Choose a new password below.');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        if (! $request->session()->has('password_reset_user_id')) {
            return redirect()
                ->route('church.password.forgot')
                ->with('error', 'Session expired. Please start again.');
        }

        $resendAttempts = (int) $request->session()->get('password_reset_resend_attempts', 0);

        if ($resendAttempts >= $this->passwordResetService->maxResendAttempts()) {
            return back()->with('error', 'Maximum resend attempts reached. Please start again.');
        }

        $user = User::findOrFail((int) $request->session()->get('password_reset_user_id'));
        $church = $user->church;

        if (! $church) {
            return redirect()->route('church.password.forgot')->with('error', 'Your account is not linked to a church.');
        }

        $identifier = (string) $request->session()->get('password_reset_login_identifier', '');
        $otp = $this->passwordResetService->resend($user, $church, $identifier, $request, $resendAttempts);

        if (! $otp) {
            return back()->with('error', 'Unable to resend verification code. Please start again.');
        }

        $request->session()->put('password_reset_resend_attempts', $resendAttempts + 1);

        return back()->with('success', 'A new verification code has been sent to your phone.');
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('password_reset_verified') || ! $request->session()->has('password_reset_user_id')) {
            return redirect()
                ->route('church.password.forgot')
                ->with('error', 'Please verify your identity before resetting your password.');
        }

        return view('church.auth.forgot-password-reset', [
            'loginIdentifier' => $request->session()->get('password_reset_login_identifier'),
        ]);
    }

    public function resetPassword(CompletePasswordResetRequest $request): RedirectResponse
    {
        if (! $request->session()->get('password_reset_verified') || ! $request->session()->has('password_reset_user_id')) {
            return redirect()
                ->route('church.password.forgot')
                ->with('error', 'Session expired. Please start again.');
        }

        $user = User::findOrFail((int) $request->session()->get('password_reset_user_id'));

        $this->passwordResetService->resetPassword($user, $request->validated('password'));

        $request->session()->forget([
            'password_reset_user_id',
            'password_reset_login_identifier',
            'password_reset_resend_attempts',
            'password_reset_verified',
        ]);

        return redirect()
            ->route('church.login')
            ->with('success', 'Your password has been reset. Sign in with your new password.');
    }
}
