<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Enums\DependantRelationship;
use App\Enums\MemberRequestType;
use App\Http\Requests\Api\MemberRequestIndexRequest;
use App\Http\Requests\Church\StoreMemberRequestRequest;
use App\Http\Resources\Api\MemberDependantResource;
use App\Http\Resources\Api\MemberRequestResource;
use App\Models\Leader;
use App\Models\MemberRequest;
use App\Services\Church\MemberRequestCertificateService;
use App\Services\Church\MemberRequestService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RequestController extends MemberPortalController
{
    public function __construct(
        private readonly MemberRequestService $memberRequestService,
        private readonly MemberRequestCertificateService $certificateService,
    ) {}

    public function index(MemberRequestIndexRequest $request): JsonResponse
    {
        $member = $this->member();
        $perPage = $request->integer('per_page') ?: 15;

        $requests = MemberRequest::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->with(['assignedLeader.member', 'responder'])
            ->latest()
            ->paginate($perPage);

        return ApiResponse::paginated(
            $requests,
            MemberRequestResource::collection($requests)->resolve(),
        );
    }

    public function meta(): JsonResponse
    {
        $member = $this->member();
        $leaders = $this->memberRequestService->assignableLeaders($member->church, $member->branch_id);

        $children = $member->familyDependants()
            ->filter(fn ($dependant) => $dependant->relationship === DependantRelationship::Child)
            ->values();

        return ApiResponse::success([
            'types' => collect(MemberRequestType::cases())->map(fn (MemberRequestType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
                'generates_certificate' => $type->generatesCertificate(),
            ])->values()->all(),
            'leaders' => $leaders->map(fn (Leader $leader) => [
                'id' => $leader->id,
                'position_label' => $leader->positionLabel(),
                'name' => $leader->member?->full_name,
            ])->values()->all(),
            'children' => MemberDependantResource::collection($children)->resolve(),
        ]);
    }

    public function store(StoreMemberRequestRequest $request): JsonResponse
    {
        $member = $this->member();

        $leader = Leader::query()->findOrFail($request->validated('assigned_leader_id'));
        abort_unless($leader->church_id === $member->church_id, 403);

        $memberRequest = $this->memberRequestService->create($member, $request->validated());
        $memberRequest->load(['assignedLeader.member', 'responder']);

        return ApiResponse::success(
            (new MemberRequestResource($memberRequest))->resolve(),
            "Request submitted successfully. Reference: {$memberRequest->reference_number}",
            201,
        );
    }

    public function show(int $id): JsonResponse
    {
        $member = $this->member();

        $memberRequest = MemberRequest::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->with(['assignedLeader.member', 'responder', 'member', 'church'])
            ->find($id);

        if (! $memberRequest) {
            return ApiResponse::error('Request not found', 404);
        }

        $this->authorize('view', $memberRequest);

        return ApiResponse::success((new MemberRequestResource($memberRequest))->resolve());
    }

    public function certificate(int $id): StreamedResponse|JsonResponse
    {
        $member = $this->member();

        $memberRequest = MemberRequest::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->find($id);

        if (! $memberRequest) {
            return ApiResponse::error('Request not found', 404);
        }

        $this->authorize('downloadCertificate', $memberRequest);

        return $this->certificateService->download($memberRequest);
    }
}
