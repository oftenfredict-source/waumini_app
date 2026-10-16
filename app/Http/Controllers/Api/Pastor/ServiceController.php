<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Requests\Api\Pastor\StorePastorServiceRequest;
use App\Http\Requests\Api\Pastor\UpdatePastorServiceRequest;
use App\Http\Resources\Api\ChurchServiceDetailResource;
use App\Http\Resources\Api\ChurchServiceResource;
use App\Models\ChurchService;
use App\Services\Church\BranchAccessService;
use App\Services\Church\ChurchServiceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends PastorController
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly ChurchServiceService $churchServiceService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ChurchService::class);

        $user = $this->pastor();
        $query = ChurchService::forChurch($user->church_id)
            ->with(['branch:id,name', 'preacherMember:id,full_name'])
            ->orderByDesc('service_date');

        if ($request->boolean('upcoming')) {
            $query->whereDate('service_date', '>=', now()->toDateString());
        }

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );
        $this->searchColumns($query, $request, ['title', 'theme', 'venue', 'preacher']);

        $services = $query->paginate($this->perPage($request));

        return $this->paginated($services, ChurchServiceResource::collection($services)->resolve());
    }

    public function people(Request $request): JsonResponse
    {
        $this->authorize('create', ChurchService::class);

        $type = $request->string('type')->toString();
        $query = $request->string('q')->trim()->toString();
        $church = $this->church();

        $data = match ($type) {
            'pastor' => $this->churchServiceService->pastorsForChurch($church),
            'leader' => $this->churchServiceService->leadersForChurch($church),
            'member' => $this->churchServiceService->searchMembers($church, $query),
            default => collect(),
        };

        return ApiResponse::success($data->values()->all());
    }

    public function store(StorePastorServiceRequest $request): JsonResponse
    {
        $service = $this->churchServiceService->create(
            $this->church(),
            $request->validated(),
            $this->pastor(),
        )->load(['branch:id,name', 'preacherMember', 'coordinatorMember']);

        return ApiResponse::success(
            (new ChurchServiceDetailResource($service))->resolve(),
            'Service created successfully.',
            201,
        );
    }

    public function show(int $id): JsonResponse
    {
        $service = ChurchService::forChurch($this->pastor()->church_id)
            ->with(['branch:id,name', 'preacherMember', 'coordinatorMember'])
            ->find($id);

        if (! $service) {
            return ApiResponse::error('Service not found', 404);
        }

        $this->authorize('view', $service);

        return ApiResponse::success((new ChurchServiceDetailResource($service))->resolve());
    }

    public function update(UpdatePastorServiceRequest $request, int $id): JsonResponse
    {
        $service = ChurchService::forChurch($this->pastor()->church_id)->find($id);

        if (! $service) {
            return ApiResponse::error('Service not found', 404);
        }

        $this->authorize('update', $service);

        $updated = $this->churchServiceService->update(
            $service,
            $request->validated(),
            $this->pastor(),
        )->load(['branch:id,name', 'preacherMember', 'coordinatorMember']);

        return ApiResponse::success(
            (new ChurchServiceDetailResource($updated))->resolve(),
            'Service updated successfully.',
        );
    }
}
