<?php

namespace App\Http\Middleware;

use App\Enums\ChurchStatus;
use App\Services\Church\MissedAttendanceNotificationService;
use App\Services\Owner\ChurchImpersonationService;
use App\Services\Owner\TrialExpiryService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChurchUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isChurchPortalUser()) {
            if ($request->expectsJson()) {
                abort(403, 'Unauthorized. Church access required.');
            }

            return redirect()->route('church.login')
                ->with('error', 'Please log in with your church account.');
        }

        $church = $user->church;

        if (! $church) {
            auth()->logout();

            return redirect()->route('church.login')
                ->with('error', 'Your church account is not linked to a church.');
        }

        $ownerImpersonating = app(ChurchImpersonationService::class)->isActive($request);

        if (! $ownerImpersonating) {
            app(TrialExpiryService::class)->expireChurchIfDue($church->fresh(), notify: true);
            $church->refresh();
        }

        if (! $ownerImpersonating && in_array($church->status, [ChurchStatus::Suspended, ChurchStatus::Expired], true)) {
            auth()->logout();

            if ($church->status === ChurchStatus::Expired) {
                return redirect()->route('church.login')
                    ->with('trial_expired', true)
                    ->with('error', __('auth.trial_expired_login'));
            }

            return redirect()->route('church.login')
                ->with('error', 'This church account is '.$church->status->value.'. Contact platform support.');
        }

        app()->instance('currentChurch', $church);
        app(MissedAttendanceNotificationService::class)->dispatchDaily($church);

        return $next($request);
    }
}
