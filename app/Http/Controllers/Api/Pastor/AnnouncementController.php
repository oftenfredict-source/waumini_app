<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Requests\Api\Pastor\StorePastorAnnouncementRequest;
use App\Http\Resources\Api\AnnouncementResource;
use App\Models\Announcement;
use App\Services\Church\AnnouncementService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends PastorController
{
    public function __construct(
        private readonly AnnouncementService $announcementService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Announcement::class);

        $query = Announcement::forChurch($this->pastor()->church_id)
            ->with('department:id,name')
            ->orderByDesc('is_pinned')
            ->latest();

        if (! $request->boolean('include_inactive')) {
            $query->where('is_active', true);
        }

        $this->searchColumns($query, $request, ['title', 'content']);

        $announcements = $query->paginate($this->perPage($request));

        return $this->paginated($announcements, AnnouncementResource::collection($announcements)->resolve());
    }

    public function store(StorePastorAnnouncementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $memberIds = $data['member_ids'] ?? [];

        $announcement = $this->announcementService->create(
            $this->church(),
            $data,
            $memberIds,
        )->load(['department:id,name', 'creator:id,name']);

        return ApiResponse::success(
            (new AnnouncementResource($announcement))->resolve(),
            'Announcement created successfully.',
            201,
        );
    }

    public function show(int $id): JsonResponse
    {
        $announcement = Announcement::forChurch($this->pastor()->church_id)
            ->with(['department:id,name', 'creator:id,name'])
            ->find($id);

        if (! $announcement) {
            return ApiResponse::error('Announcement not found', 404);
        }

        $this->authorize('view', $announcement);

        return ApiResponse::success((new AnnouncementResource($announcement))->resolve());
    }
}
