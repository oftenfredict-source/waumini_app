<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Church;
use App\Services\Owner\OwnerSmsUsageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsUsageController extends Controller
{
    public function __construct(
        private readonly OwnerSmsUsageService $smsUsageService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('churches.view'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $month = $this->resolveMonth($request);

        return view('owner.sms-usage.index', [
            'month' => $month,
            'monthInput' => $month->format('Y-m'),
            'totals' => $this->smsUsageService->platformTotals($month),
            'churches' => $this->smsUsageService->churchesUsage($month),
        ]);
    }

    public function show(Request $request, Church $church): View
    {
        $month = $this->resolveMonth($request);
        $church->load('activeSubscription.package');

        return view('owner.sms-usage.show', [
            'church' => $church,
            'month' => $month,
            'monthInput' => $month->format('Y-m'),
            'summary' => $this->smsUsageService->churchSummary($church, $month),
            'messages' => $this->smsUsageService->churchMessages($church, $month),
        ]);
    }

    private function resolveMonth(Request $request): Carbon
    {
        $value = $request->string('month')->trim()->toString();

        if ($value !== '' && preg_match('/^\d{4}-\d{2}$/', $value)) {
            return Carbon::createFromFormat('Y-m', $value)->startOfMonth();
        }

        return now()->startOfMonth();
    }
}
