<?php

namespace App\Http\Controllers\Church\System;

use App\Services\Church\SystemMonitorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitorController extends SystemController
{
    public function __construct(
        private readonly SystemMonitorService $systemMonitorService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.monitor'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $this->church();

        return view('church.system.monitor.index', [
            'church' => $church,
            'stats' => $this->systemMonitorService->build($church),
        ]);
    }
}
