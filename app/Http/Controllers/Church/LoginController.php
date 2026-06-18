<?php

namespace App\Http\Controllers\Church;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Church\LeadershipStaffAccessService;
use App\Services\Church\LoginOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(
        private readonly LoginOtpService $loginOtpService,
    ) {}

    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isChurchPortalUser()) {
            return $this->redirectAfterLogin(Auth::user());
        }

        return view('church.auth.login', [
            'ownerSessionActive' => Auth::check() && Auth::user()->isOwnerUser(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::check()) {
            Auth::logout();
        }

        $identifier = trim($credentials['email']);
        $password = rtrim($credentials['password']);
        $user = User::findByLoginIdentifier($identifier);

        if (! $user || ! Hash::check($password, $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Invalid member ID or password.');
        }

        if ($user->status !== UserStatus::Active) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Your account is not active. Contact your church administrator.');
        }

        if (! $user->isChurchPortalUser()) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'This login is for church members and staff. Platform owners should use /owner/login.');
        }

        $church = $user->church;

        if (! $church) {
            return back()->with('error', 'Your account is not linked to a church.');
        }

        if ($church->status->value === 'suspended') {
            return back()->with('error', 'This church has been suspended. Contact platform support.');
        }

        if ($this->loginOtpService->isEnabledForChurch($church)) {
            if (empty($user->loginPhone())) {
                return back()
                    ->withInput($request->only('email'))
                    ->with('error', 'OTP login is enabled but your account has no phone number. Contact your church administrator.');
            }

            $otp = $this->loginOtpService->issue($user, $church, $identifier, $request);

            if (! $otp) {
                return back()
                    ->withInput($request->only('email'))
                    ->with('error', 'Unable to send OTP to your phone. Please try again or contact your church administrator.');
            }

            $request->session()->put([
                'otp_user_id' => $user->id,
                'otp_login_identifier' => $identifier,
                'otp_remember' => $request->boolean('remember'),
                'otp_resend_attempts' => 0,
            ]);

            return redirect()
                ->route('church.login.otp')
                ->with('info', 'A verification code has been sent to your phone. Enter it below to complete sign in. If you did not receive it, ask your church administrator to view the code under System → OTP Management.');
        }

        return $this->completeLogin($user, $request);
    }

    public function showOtpForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('otp_user_id')) {
            return redirect()
                ->route('church.login')
                ->with('error', 'Please sign in first to receive a verification code.');
        }

        $userId = (int) $request->session()->get('otp_user_id');
        $otp = $this->loginOtpService->activeOtpForUser($userId);

        return view('church.auth.otp', [
            'loginIdentifier' => $request->session()->get('otp_login_identifier'),
            'otpExpiresAt' => $otp?->expires_at,
            'resendAttempts' => (int) $request->session()->get('otp_resend_attempts', 0),
            'maxResendAttempts' => $this->loginOtpService->maxResendAttempts(),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        if (! $request->session()->has('otp_user_id')) {
            return redirect()
                ->route('church.login')
                ->with('error', 'Session expired. Please sign in again.');
        }

        $userId = (int) $request->session()->get('otp_user_id');
        $result = $this->loginOtpService->verify($request->input('otp'), $userId);

        if (! ($result['success'] ?? false)) {
            return back()->with('error', $result['message'] ?? 'Invalid verification code.');
        }

        /** @var User $user */
        $user = $result['user'];
        $remember = (bool) $request->session()->get('otp_remember', false);
        $request->session()->forget(['otp_user_id', 'otp_login_identifier', 'otp_remember', 'otp_resend_attempts']);

        Auth::login($user, $remember);

        return $this->completeLogin($user, $request, skipPasswordChecks: true);
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        if (! $request->session()->has('otp_user_id')) {
            return redirect()
                ->route('church.login')
                ->with('error', 'Session expired. Please sign in again.');
        }

        $resendAttempts = (int) $request->session()->get('otp_resend_attempts', 0);

        if ($resendAttempts >= $this->loginOtpService->maxResendAttempts()) {
            return back()->with('error', 'Maximum resend attempts reached. Please sign in again.');
        }

        $user = User::findOrFail((int) $request->session()->get('otp_user_id'));
        $church = $user->church;

        if (! $church) {
            return redirect()->route('church.login')->with('error', 'Your account is not linked to a church.');
        }

        $identifier = (string) $request->session()->get('otp_login_identifier', '');
        $otp = $this->loginOtpService->resend($user, $church, $identifier, $request, $resendAttempts);

        if (! $otp) {
            return back()->with('error', 'Unable to resend verification code. Please sign in again.');
        }

        $request->session()->put('otp_resend_attempts', $resendAttempts + 1);

        return back()->with('success', 'A new verification code has been sent to your phone.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('church.login');
    }

    private function completeLogin(User $user, Request $request, bool $skipPasswordChecks = false): RedirectResponse
    {
        if (! $skipPasswordChecks) {
            Auth::login($user, $request->boolean('remember'));
        }

        $user->update(['last_login_at' => now()]);

        if ($user->member) {
            app(LeadershipStaffAccessService::class)->refreshMemberAccess($user->member);
            $user->refresh();
        }

        $request->session()->regenerate();

        return $this->redirectAfterLogin($user);
    }

    private function redirectAfterLogin(User $user): RedirectResponse
    {
        if ($user->isChurchMember() && $user->member) {
            return redirect()->intended(route('church.member.dashboard'));
        }

        return redirect()->intended(route('church.dashboard'));
    }
}
