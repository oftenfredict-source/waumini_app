<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Services\Church\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('analytics.view'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;
        $analytics = $this->analyticsService->build($church);

        return view('church.analytics.index', [
            'church' => $church,
            'analytics' => $analytics,
            'canViewFinance' => $request->user()->can('finance.view'),
        ]);
    }
}
