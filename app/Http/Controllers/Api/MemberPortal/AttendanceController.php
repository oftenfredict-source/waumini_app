<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Services\Church\AttendanceService;
use App\Services\Church\MemberPortalService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends MemberPortalController
{
    public function __construct(
        private readonly MemberPortalService $memberPortalService,
        private readonly AttendanceService $attendanceService,
    ) {}

    public function index(): JsonResponse
    {
        return ApiResponse::success(
            $this->memberPortalService->attendanceFor($this->member())
        );
    }

    public function scan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payload' => ['required', 'string', 'max:500'],
        ]);

        $result = $this->attendanceService->recordMemberScan(
            $this->member(),
            $data['payload'],
        );

        $message = $result['already_recorded']
            ? __('pages.attendance.already_scanned')
            : __('pages.attendance.scanned_ok');

        return ApiResponse::success($result, $message);
    }
}
