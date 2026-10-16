<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Requests\Api\Pastor\StorePastorEventRequest;
use App\Http\Requests\Api\Pastor\UpdatePastorEventRequest;
use App\Http\Resources\Api\SpecialEventResource;
use App\Models\SpecialEvent;
use App\Services\Church\BranchAccessService;
use App\Services\Church\SpecialEventService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends PastorController
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly SpecialEventService $specialEventService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SpecialEvent::class);

        $user = $this->pastor();
        $query = SpecialEvent::forChurch($user->church_id)
            ->with(['branch:id,name', 'creator:id,name'])
            ->orderByDesc('event_date')
            ->orderByDesc('start_time');

        if ($request->boolean('upcoming')) {
            $query->whereDate('event_date', '>=', now()->toDateString())
                ->where('status', '!=', 'cancelled');
        }

        if ($status = trim($request->string('status')->toString())) {
            $query->where('status', $status);
        }

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );
        $this->searchColumns($query, $request, ['title', 'venue', 'speaker', 'category_other']);

        $events = $query->paginate($this->perPage($request));
        $events->getCollection()->each->syncLiveStatus();

        return $this->paginated($events, SpecialEventResource::collection($events)->resolve());
    }

    public function store(StorePastorEventRequest $request): JsonResponse
    {
        $event = $this->specialEventService->create(
            $this->church(),
            $request->validated(),
            $this->pastor(),
        )->load(['branch:id,name', 'creator:id,name']);

        return ApiResponse::success(
            (new SpecialEventResource($event))->resolve(),
            'Event created successfully.',
            201,
        );
    }

    public function show(int $id): JsonResponse
    {
        $event = $this->eventRecord($id);

        if (! $event) {
            return ApiResponse::error('Event not found', 404);
        }

        $this->authorize('view', $event);
        $event->syncLiveStatus();

        return ApiResponse::success((new SpecialEventResource($event))->resolve());
    }

    public function update(UpdatePastorEventRequest $request, int $id): JsonResponse
    {
        $event = $request->eventRecord() ?? $this->eventRecord($id);

        if (! $event) {
            return ApiResponse::error('Event not found', 404);
        }

        $this->authorize('update', $event);

        $updated = $this->specialEventService->update(
            $event,
            $request->validated(),
            $this->pastor(),
        )->load(['branch:id,name', 'creator:id,name']);

        return ApiResponse::success(
            (new SpecialEventResource($updated))->resolve(),
            'Event updated successfully.',
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $event = $this->eventRecord($id);

        if (! $event) {
            return ApiResponse::error('Event not found', 404);
        }

        $this->authorize('delete', $event);

        $title = $event->title;
        $this->specialEventService->delete($event);

        return ApiResponse::success(
            ['id' => $id],
            "{$title} has been deleted.",
        );
    }

    private function eventRecord(int $id): ?SpecialEvent
    {
        return SpecialEvent::forChurch($this->pastor()->church_id)
            ->with(['branch:id,name', 'creator:id,name'])
            ->find($id);
    }
}
