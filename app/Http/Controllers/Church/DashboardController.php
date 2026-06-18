<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Services\Church\ChurchDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ChurchDashboardService $churchDashboardService,
    ) {}

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isChurchMember()) {
            return redirect()->route('church.member.dashboard');
        }

        if ($user->hasLinkedMember()) {
            $user->load('member');
        }

        $church = $user->church;
        $church->load(['activeSubscription.package', 'primaryDomain']);

        $dashboard = $this->churchDashboardService->build($church, $user);

        return view('church.dashboard.index', [
            'church' => $church,
            'user' => $user,
            'dashboard' => $dashboard,
        ]);
    }
}
