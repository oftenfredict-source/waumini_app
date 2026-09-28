<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Services\Church\MemberPortalService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class GivingController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(
            $this->memberPortalService->givingFor($this->member())
        );
    }
}
