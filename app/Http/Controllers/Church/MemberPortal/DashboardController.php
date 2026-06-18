<?php

namespace App\Http\Controllers\Church\MemberPortal;

use App\Services\Church\MemberPortalService;
use Illuminate\View\View;

class DashboardController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
    ) {}

    public function index(): View
    {
        $member = $this->member()->load(['church', 'departments', 'spouseMember', 'dependants']);

        return view('church.member-portal.dashboard', [
            'dashboard' => $this->memberPortalService->buildDashboard($member),
            'member' => $member,
            'church' => $member->church,
        ]);
    }
}
