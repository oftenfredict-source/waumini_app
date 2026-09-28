<?php

namespace App\Http\Controllers\Church\MemberPortal;

use App\Models\ChurchService;
use App\Services\Church\MemberPortalService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
    ) {}

    public function index(Request $request): View
    {
        $member = $this->member();

        $services = $this->memberPortalService
            ->servicesForChurch($member->church_id)
            ->paginate(15)
            ->withQueryString();
        $services->getCollection()->each->syncLiveStatus();

        return view('church.member-portal.services.index', [
            'services' => $services,
            'church' => $member->church,
        ]);
    }

    public function show(ChurchService $service): View
    {
        $member = $this->member();
        abort_unless($service->church_id === $member->church_id, 404);

        $service->load(['branch', 'preacherMember', 'coordinatorMember']);
        $service->syncLiveStatus();

        return view('church.member-portal.services.show', [
            'service' => $service,
            'church' => $member->church,
        ]);
    }
}
