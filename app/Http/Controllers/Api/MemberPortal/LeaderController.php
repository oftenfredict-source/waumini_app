<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Resources\Api\LeaderResource;
use App\Services\Church\MemberPortalService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class LeaderController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
    ) {}

    public function index(): JsonResponse
    {
        $member = $this->member();

        return ApiResponse::success(
            LeaderResource::collection(
                $this->memberPortalService->activeLeaders($member->church_id)
            )->resolve()
        );
    }
}
