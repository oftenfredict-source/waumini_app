<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Http\Requests\Church\ApproveFinancialRecordRequest;
use App\Http\Requests\Church\BulkApproveFinancialRecordsRequest;
use App\Http\Requests\Church\RejectFinancialRecordRequest;
use App\Services\Church\FinanceApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class FinanceApprovalController extends Controller
{
    public function __construct(
        private readonly FinanceApprovalService $financeApprovalService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('finance.approve'), 403);

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $church = $request->user()->church;
        $dashboard = $this->financeApprovalService->buildDashboard($church->id);

        return view('church.finance.approvals.dashboard', [
            'church' => $church,
            'dashboard' => $dashboard,
            'types' => $this->financeApprovalService->types(),
            'canApprove' => $request->user()->can('finance.approve'),
        ]);
    }

    public function approve(ApproveFinancialRecordRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $this->financeApprovalService->approve(
                $request->user()->church_id,
                $request->validated('type'),
                (int) $request->validated('id'),
                $request->user(),
                $request->validated('approval_notes')
            );
        } catch (InvalidArgumentException $e) {
            return $this->respond($request, false, $e->getMessage());
        }

        return $this->respond($request, true, 'Record approved successfully.');
    }

    public function reject(RejectFinancialRecordRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $this->financeApprovalService->reject(
                $request->user()->church_id,
                $request->validated('type'),
                (int) $request->validated('id'),
                $request->user(),
                $request->validated('rejection_reason')
            );
        } catch (InvalidArgumentException $e) {
            return $this->respond($request, false, $e->getMessage());
        }

        return $this->respond($request, true, 'Record rejected successfully.');
    }

    public function bulkApprove(BulkApproveFinancialRecordsRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $count = $this->financeApprovalService->bulkApprove(
                $request->user()->church_id,
                $request->validated('records'),
                $request->user(),
                $request->validated('approval_notes')
            );
        } catch (InvalidArgumentException $e) {
            return $this->respond($request, false, $e->getMessage());
        }

        return $this->respond($request, true, "Successfully approved {$count} record(s).");
    }

    public function details(Request $request, string $type, int $id): JsonResponse
    {
        try {
            $details = $this->financeApprovalService->recordDetails(
                $request->user()->church_id,
                $type,
                $id
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $details]);
    }

    private function respond(Request $request, bool $success, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['success' => $success, 'message' => $message], $success ? 200 : 422);
        }

        return redirect()
            ->route('church.finance.approvals')
            ->with($success ? 'success' : 'error', $message);
    }
}
