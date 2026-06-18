<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Services\Church\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('reports.view'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;
        [$start, $end] = $this->reportService->parseDateRange($request);

        return view('church.reports.index', [
            'church' => $church,
            'start' => $start,
            'end' => $end,
            'summary' => $this->reportService->indexSummary($church, $start, $end),
        ]);
    }

    public function overview(Request $request): View
    {
        $church = $request->user()->church;
        [$start, $end] = $this->reportService->parseDateRange($request);

        return view('church.reports.overview', [
            'church' => $church,
            'start' => $start,
            'end' => $end,
            'report' => $this->reportService->overview($church, $start, $end),
        ]);
    }

    public function memberSummary(Request $request): View
    {
        $church = $request->user()->church;

        return view('church.reports.member-summary', [
            'church' => $church,
            'report' => $this->reportService->memberSummary($church),
        ]);
    }

    public function memberGiving(Request $request): View
    {
        $church = $request->user()->church;
        [$start, $end] = $this->reportService->parseDateRange($request);
        $memberId = $request->integer('member_id') ?: null;

        return view('church.reports.member-giving', [
            'church' => $church,
            'start' => $start,
            'end' => $end,
            'report' => $this->reportService->memberGiving($church, $start, $end, $memberId),
        ]);
    }

    public function incomeVsExpenditure(Request $request): View
    {
        $church = $request->user()->church;
        [$start, $end] = $this->reportService->parseDateRange($request);

        return view('church.reports.income-vs-expenditure', [
            'church' => $church,
            'start' => $start,
            'end' => $end,
            'report' => $this->reportService->incomeVsExpenditure($church, $start, $end),
        ]);
    }

    public function offeringBreakdown(Request $request): View
    {
        $church = $request->user()->church;
        [$start, $end] = $this->reportService->parseDateRange($request);

        return view('church.reports.offering-breakdown', [
            'church' => $church,
            'start' => $start,
            'end' => $end,
            'report' => $this->reportService->offeringBreakdown($church, $start, $end),
        ]);
    }

    public function budgetPerformance(Request $request): View
    {
        $church = $request->user()->church;
        $budgetId = $request->integer('budget_id') ?: null;

        return view('church.reports.budget-performance', [
            'church' => $church,
            'report' => $this->reportService->budgetPerformance($church, $budgetId),
        ]);
    }

    public function attendanceSummary(Request $request): View
    {
        $church = $request->user()->church;
        [$start, $end] = $this->reportService->parseDateRange($request);

        return view('church.reports.attendance-summary', [
            'church' => $church,
            'start' => $start,
            'end' => $end,
            'report' => $this->reportService->attendanceSummary($church, $start, $end),
        ]);
    }

    public function leadership(Request $request): View
    {
        $church = $request->user()->church;

        return view('church.reports.leadership', [
            'church' => $church,
            'report' => $this->reportService->leadership($church),
        ]);
    }

    public function monthlyFinancial(Request $request): View
    {
        $church = $request->user()->church;
        $period = $request->string('period')->trim()->toString();
        $year = (int) now()->year;
        $month = (int) now()->month;

        if ($period && preg_match('/^\d{4}-\d{2}$/', $period)) {
            [$year, $month] = array_map('intval', explode('-', $period));
        }

        return view('church.reports.monthly-financial', [
            'church' => $church,
            'report' => $this->reportService->monthlyFinancial($church, $year, $month),
        ]);
    }
}
