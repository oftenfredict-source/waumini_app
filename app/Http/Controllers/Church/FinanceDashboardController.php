<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Services\Church\FinanceDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceDashboardController extends Controller
{
    public function __construct(
        private readonly FinanceDashboardService $financeDashboardService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('finance.view'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;
        $period = $request->string('period')->trim()->toString();

        $year = null;
        $month = null;

        if ($period && preg_match('/^\d{4}-\d{2}$/', $period)) {
            [$year, $month] = array_map('intval', explode('-', $period));
        }

        $dashboard = $this->financeDashboardService->build($church, $year, $month);

        return view('church.finance.dashboard', [
            'church' => $church,
            'dashboard' => $dashboard,
            'canApprove' => $request->user()->can('finance.approve'),
            'canManage' => $request->user()->can('finance.manage'),
        ]);
    }
}
