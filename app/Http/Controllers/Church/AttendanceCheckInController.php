<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Services\Church\AttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceCheckInController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
    ) {}

    public function __invoke(Request $request): View|RedirectResponse
    {
        if (! $request->user()) {
            return redirect()->guest(route('church.login'));
        }

        if (! $request->user()->canAccessMemberPortal()) {
            return view('church.attendance.check-in', [
                'ok' => false,
                'already' => false,
                'title' => __('pages.attendance.checkin_title'),
                'message' => __('pages.attendance.checkin_member_only'),
                'serviceTitle' => null,
            ]);
        }

        try {
            $result = $this->attendanceService->recordMemberScan(
                $request->user()->member,
                $request->fullUrl(),
            );
        } catch (ValidationException $e) {
            return view('church.attendance.check-in', [
                'ok' => false,
                'already' => false,
                'title' => __('pages.attendance.checkin_title'),
                'message' => collect($e->errors())->flatten()->first() ?: __('pages.attendance.invalid_qr'),
                'serviceTitle' => null,
            ]);
        }

        return view('church.attendance.check-in', [
            'ok' => true,
            'already' => (bool) $result['already_recorded'],
            'title' => __('pages.attendance.checkin_title'),
            'message' => $result['already_recorded']
                ? __('pages.attendance.already_scanned')
                : __('pages.attendance.scanned_ok'),
            'serviceTitle' => $result['title'],
        ]);
    }
}
