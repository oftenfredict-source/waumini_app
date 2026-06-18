<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\Owner\DashboardService;
use Illuminate\View\View;

class RevenueController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', \App\Models\Payment::class);

        return view('owner.revenue.index', [
            'overview' => $this->dashboardService->overview(),
            'signupsChart' => $this->dashboardService->signupsChart(),
            'churchesByPackage' => $this->dashboardService->churchesByPackage(),
            'monthlyRevenue' => $this->dashboardService->monthlyRevenueChart(),
        ]);
    }
}
