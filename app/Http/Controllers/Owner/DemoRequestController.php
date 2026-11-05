<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\DemoRequest;
use App\Models\SubscriptionPackage;
use App\Services\Owner\DemoRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemoRequestController extends Controller
{
    public function __construct(
        private readonly DemoRequestService $demoRequestService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DemoRequest::class);

        $query = DemoRequest::query()
            ->with(['package', 'church', 'reviewer'])
            ->latest();

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        return view('owner.demo-requests.index', [
            'requests' => $query->paginate(15)->withQueryString(),
            'stats' => [
                'pending' => DemoRequest::pending()->count(),
                'approved' => DemoRequest::where('status', DemoRequest::STATUS_APPROVED)->count(),
                'rejected' => DemoRequest::where('status', DemoRequest::STATUS_REJECTED)->count(),
            ],
            'packages' => SubscriptionPackage::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'filters' => $request->only(['status']),
        ]);
    }

    public function approve(Request $request, DemoRequest $demoRequest): RedirectResponse
    {
        $this->authorize('approve', $demoRequest);

        $data = $request->validate([
            'package_id' => ['nullable', 'integer', 'exists:subscription_packages,id'],
        ]);

        $result = $this->demoRequestService->approve(
            $demoRequest,
            $request->user(),
            $data['package_id'] ?? null
        );

        $church = $result['request']->church;

        return redirect()
            ->route('owner.demo-requests.index', ['status' => 'approved'])
            ->with('success', __('owner.demo.approved_success', ['church' => $church?->name ?? $demoRequest->church_name]))
            ->with('admin_credentials', [
                'email' => $demoRequest->email,
                'password' => $result['admin_password'],
                'church' => $church?->name,
            ]);
    }

    public function reject(Request $request, DemoRequest $demoRequest): RedirectResponse
    {
        $this->authorize('reject', $demoRequest);

        $data = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->demoRequestService->reject(
            $demoRequest,
            $request->user(),
            $data['rejection_reason'] ?? null
        );

        return redirect()
            ->route('owner.demo-requests.index', ['status' => 'rejected'])
            ->with('success', __('owner.demo.rejected_success'));
    }
}
