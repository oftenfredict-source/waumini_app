<?php

namespace App\Http\Middleware;

use App\Enums\ChurchStatus;
use App\Services\Church\MissedAttendanceNotificationService;
use App\Services\Owner\TrialExpiryService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChurchApiUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isChurchPortalUser()) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Church access required.',
            ], 403);
        }

        $church = $user->church;

        if (! $church) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not linked to a church.',
            ], 403);
        }

        app(TrialExpiryService::class)->expireChurchIfDue($church->fresh(), notify: true);
        $church->refresh();

        if (in_array($church->status, [ChurchStatus::Suspended, ChurchStatus::Expired], true)) {
            $message = $church->status === ChurchStatus::Expired
                ? __('auth.trial_expired_login')
                : 'This church account is '.$church->status->value.'. Contact platform support.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'code' => $church->status === ChurchStatus::Expired ? 'trial_expired' : $church->status->value,
            ], 403);
        }

        app()->instance('currentChurch', $church);
        app(MissedAttendanceNotificationService::class)->dispatchDaily($church);

        return $next($request);
    }
}
