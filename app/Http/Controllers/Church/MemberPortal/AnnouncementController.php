<?php

namespace App\Http\Controllers\Church\MemberPortal;

use App\Models\Announcement;
use App\Services\Church\MemberPortalService;
use Illuminate\View\View;

class AnnouncementController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
    ) {}

    public function index(): View
    {
        $member = $this->member();

        return view('church.member-portal.announcements.index', [
            'announcements' => $this->memberPortalService->announcementsFor($member),
            'church' => $member->church,
        ]);
    }

    public function show(Announcement $announcement): View
    {
        $member = $this->member();
        abort_unless($announcement->church_id === $member->church_id, 404);
        abort_unless($announcement->isCurrentlyActive(), 404);

        $visible = Announcement::forChurch($member->church_id)
            ->active()
            ->targetedForMember($member)
            ->whereKey($announcement->id)
            ->exists();

        abort_unless($visible, 403);

        $announcement->load(['creator', 'department']);

        return view('church.member-portal.announcements.show', [
            'announcement' => $announcement,
            'church' => $member->church,
        ]);
    }
}
