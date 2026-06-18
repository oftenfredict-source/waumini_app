<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\Owner\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        return view('owner.dashboard.index', [
            'overview' => $this->dashboardService->overview(),
            'signupsChart' => $this->dashboardService->signupsChart(),
            'statusBreakdown' => $this->dashboardService->statusBreakdown(),
            'recentChurches' => $this->dashboardService->recentChurches(),
            'churchesByPackage' => $this->dashboardService->churchesByPackage(),
        ]);
    }
}
