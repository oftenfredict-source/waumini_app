<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Resources\Api\ChurchServiceDetailResource;
use App\Http\Resources\Api\ChurchServiceResource;
use App\Services\Church\MemberPortalService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $member = $this->member();
        $perPage = min(max($request->integer('per_page', 15), 1), 100);

        $query = $this->memberPortalService->servicesForChurch($member->church_id);

        if ($request->boolean('upcoming')) {
            $query->whereDate('service_date', '>=', now()->toDateString())
                ->reorder()
                ->orderBy('service_date')
                ->orderBy('start_time');
        }

        $services = $query->paginate($perPage);
        $services->getCollection()->each->syncLiveStatus();

        return ApiResponse::paginated(
            $services,
            ChurchServiceResource::collection($services)->resolve(),
        );
    }

    public function show(int $id): JsonResponse
    {
        $member = $this->member();
        $service = $this->memberPortalService->findServiceForChurch($member->church_id, $id);

        if (! $service) {
            return ApiResponse::error('Service not found', 404);
        }

        $service->syncLiveStatus();

        return ApiResponse::success((new ChurchServiceDetailResource($service))->resolve());
    }
}
