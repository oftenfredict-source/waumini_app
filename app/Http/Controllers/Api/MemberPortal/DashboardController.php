<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Resources\Api\AnnouncementResource;
use App\Http\Resources\Api\ChurchServiceResource;
use App\Http\Resources\Api\LeaderResource;
use App\Http\Resources\Api\MemberRequestResource;
use App\Http\Resources\Api\MemberResource;
use App\Services\Church\MemberPortalService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
    ) {}

    public function index(): JsonResponse
    {
        $member = $this->member()->load(['church', 'departments', 'spouseMember', 'dependants']);
        $dashboard = $this->memberPortalService->buildDashboard($member);
        $church = $member->church;

        return ApiResponse::success([
            'member' => (new MemberResource($member))->resolve(),
            'church' => [
                'id' => $church->id,
                'name' => $church->name,
                'currency' => $church->currency ?? 'TZS',
                'phone' => $church->phone,
                'email' => $church->email,
            ],
            'stats' => [
                'membership_type' => $member->membership_type?->value,
                'status' => $member->status?->value,
                'tithes_year' => $dashboard['giving']['tithes_year'],
                'offerings_year' => $dashboard['giving']['offerings_year'],
                'open_requests' => $dashboard['open_requests'],
                'year' => (int) now()->year,
            ],
            'recent_requests' => MemberRequestResource::collection($dashboard['recent_requests'])->resolve(),
            'announcements' => AnnouncementResource::collection($dashboard['announcements'])->resolve(),
            'upcoming_services' => ChurchServiceResource::collection($dashboard['upcoming_services'])->resolve(),
            'leaders' => LeaderResource::collection($dashboard['leaders'])->resolve(),
        ]);
    }
}
