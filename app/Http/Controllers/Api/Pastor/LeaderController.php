<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Requests\Api\Pastor\DeactivatePastorLeaderRequest;
use App\Http\Requests\Church\StoreLeaderRequest;
use App\Http\Resources\Api\LeaderResource;
use App\Models\Leader;
use App\Services\Church\BranchAccessService;
use App\Services\Church\LeaderService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderController extends PastorController
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly LeaderService $leaderService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Leader::class);

        $user = $this->pastor();
        $query = Leader::forChurch($user->church_id)
            ->with(['member:id,full_name,member_number,phone_number', 'branch:id,name'])
            ->latest('appointment_date');

        if ($status = trim($request->string('status')->toString())) {
            if ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status !== 'all') {
                $query->active();
            }
        } elseif (! $request->boolean('include_inactive')) {
            $query->active();
        }

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        $search = trim($request->string('search')->toString());
        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('position_title', 'like', "%{$search}%")
                    ->orWhereHas('member', function ($member) use ($search) {
                        $member->where('full_name', 'like', "%{$search}%")
                            ->orWhere('member_number', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        $leaders = $query->paginate($this->perPage($request));

        return $this->paginated($leaders, LeaderResource::collection($leaders)->resolve());
    }

    public function show(int $id): JsonResponse
    {
        $leader = Leader::forChurch($this->pastor()->church_id)
            ->with(['member', 'branch:id,name'])
            ->find($id);

        if (! $leader) {
            return ApiResponse::error('Leader not found', 404);
        }

        $this->authorize('view', $leader);

        return ApiResponse::success((new LeaderResource($leader))->resolve());
    }

    public function store(StoreLeaderRequest $request): JsonResponse
    {
        $leader = $this->leaderService->assign($this->church(), $request->validated())
            ->load(['member', 'branch:id,name']);

        return ApiResponse::success(
            (new LeaderResource($leader))->resolve(),
            'Leadership position assigned successfully.',
            201,
        );
    }

    public function deactivate(DeactivatePastorLeaderRequest $request, int $id): JsonResponse
    {
        $leader = Leader::forChurch($this->pastor()->church_id)
            ->with(['member', 'branch:id,name'])
            ->find($id);

        if (! $leader) {
            return ApiResponse::error('Leader not found', 404);
        }

        $this->authorize('deactivate', $leader);

        $updated = $this->leaderService
            ->deactivate($leader, $request->validated('reason'))
            ->load(['member', 'branch:id,name']);

        return ApiResponse::success(
            (new LeaderResource($updated))->resolve(),
            'Leadership assignment ended successfully.',
        );
    }
}
