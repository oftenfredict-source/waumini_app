<?php

namespace App\Http\Middleware;

use App\Enums\ChurchStatus;
use App\Services\Owner\ChurchImpersonationService;
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

        if (! $ownerImpersonating && in_array($church->status, [ChurchStatus::Suspended, ChurchStatus::Expired], true)) {
            auth()->logout();

            return redirect()->route('church.login')
                ->with('error', 'This church account is '.$church->status->value.'. Contact platform support.');
        }

        app()->instance('currentChurch', $church);

        return $next($request);
    }
}
