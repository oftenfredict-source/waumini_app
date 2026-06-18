<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
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

        if ($request->routeIs('church.logout')) {
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
