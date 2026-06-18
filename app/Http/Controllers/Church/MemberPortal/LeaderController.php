<?php

namespace App\Http\Controllers\Church\MemberPortal;

use App\Services\Church\MemberPortalService;
use Illuminate\View\View;

class LeaderController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
    ) {}

    public function index(): View
    {
        $member = $this->member();

        return view('church.member-portal.leaders.index', [
            'leaders' => $this->memberPortalService->activeLeaders($member->church_id),
            'church' => $member->church,
        ]);
    }
}
