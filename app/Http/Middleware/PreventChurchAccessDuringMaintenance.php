<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use App\Services\Owner\ChurchImpersonationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventChurchAccessDuringMaintenance
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! SystemSetting::churchMaintenanceEnabled()) {
            return $next($request);
        }

        if ($request->routeIs('church.logout', 'church.impersonation.leave')) {
            return $next($request);
        }

        if (app(ChurchImpersonationService::class)->isActive($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(503, SystemSetting::churchMaintenanceMessage());
        }

        if (auth()->check() && auth()->user()->isChurchPortalUser()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->view('church.maintenance', [
            'message' => SystemSetting::churchMaintenanceMessage(),
        ], 503);
    }
}
