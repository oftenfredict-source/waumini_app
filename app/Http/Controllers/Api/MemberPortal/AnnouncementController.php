<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Resources\Api\AnnouncementResource;
use App\Models\Announcement;
use App\Services\Church\MemberPortalService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AnnouncementController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
    ) {}

    public function index(): JsonResponse
    {
        $member = $this->member();

        return ApiResponse::success(
            AnnouncementResource::collection(
                $this->memberPortalService->announcementsFor($member)
            )->resolve()
        );
    }

    public function show(int $id): JsonResponse
    {
        $member = $this->member();

        $announcement = Announcement::forChurch($member->church_id)->find($id);

        if (! $announcement || ! $announcement->isCurrentlyActive()) {
            return ApiResponse::error('Announcement not found', 404);
        }

        $visible = Announcement::forChurch($member->church_id)
            ->active()
            ->targetedForMember($member)
            ->whereKey($announcement->id)
            ->exists();

        if (! $visible) {
            return ApiResponse::error('Forbidden', 403);
        }

        $announcement->load(['creator', 'department']);

        return ApiResponse::success((new AnnouncementResource($announcement))->resolve());
    }
}
