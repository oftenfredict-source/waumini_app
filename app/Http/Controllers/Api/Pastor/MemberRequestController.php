<?php

namespace App\Http\Controllers\Api\Pastor;

use App\Http\Requests\Api\Pastor\RespondPastorMemberRequest;
use App\Http\Resources\Api\MemberRequestResource;
use App\Models\MemberRequest;
use App\Services\Church\BranchAccessService;
use App\Services\Church\MemberRequestService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberRequestController extends PastorController
{
    public function __construct(
        private readonly BranchAccessService $branchAccessService,
        private readonly MemberRequestService $memberRequestService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MemberRequest::class);

        $user = $this->pastor();
        $query = MemberRequest::forChurch($user->church_id)
            ->with([
                'member:id,full_name,member_number,phone_number',
                'assignedLeader.member:id,full_name',
            ])
            ->latest();

        if ($status = trim($request->string('status')->toString())) {
            $query->where('status', $status);
        }

        if ($type = trim($request->string('type')->toString())) {
            $query->where('type', $type);
        }

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );
        $this->searchColumns($query, $request, ['reference_number', 'subject', 'description']);

        $requests = $query->paginate($this->perPage($request));

        return $this->paginated($requests, MemberRequestResource::collection($requests)->resolve());
    }

    public function show(int $id): JsonResponse
    {
        $memberRequest = MemberRequest::forChurch($this->pastor()->church_id)
            ->with([
                'member:id,full_name,member_number,phone_number',
                'assignedLeader.member:id,full_name',
            ])
            ->find($id);

        if (! $memberRequest) {
            return ApiResponse::error('Member request not found', 404);
        }

        $this->authorize('view', $memberRequest);

        return ApiResponse::success((new MemberRequestResource($memberRequest))->resolve());
    }

    public function respond(RespondPastorMemberRequest $request, int $id): JsonResponse
    {
        $memberRequest = MemberRequest::forChurch($this->pastor()->church_id)->find($id);

        if (! $memberRequest) {
            return ApiResponse::error('Member request not found', 404);
        }

        $this->authorize('respond', $memberRequest);

        $updated = $this->memberRequestService->respond(
            $memberRequest,
            $this->pastor(),
            $request->validated('status'),
            $request->validated('response'),
        )->load([
            'member:id,full_name,member_number,phone_number',
            'assignedLeader.member:id,full_name',
        ]);

        return ApiResponse::success(
            (new MemberRequestResource($updated))->resolve(),
            'Request updated successfully.',
        );
    }
}
