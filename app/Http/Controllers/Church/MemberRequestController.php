<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Http\Requests\Church\RespondMemberRequestRequest;
use App\Models\MemberRequest;
use App\Services\Church\MemberRequestService;
use App\Services\Church\MemberRequestCertificateService;
use App\Services\Church\BranchAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberRequestController extends Controller
{
    public function __construct(
        private readonly MemberRequestService $memberRequestService,
        private readonly MemberRequestCertificateService $certificateService,
        private readonly BranchAccessService $branchAccessService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MemberRequest::class);

        $church = auth()->user()->church;
        $user = auth()->user();

        $query = MemberRequest::forChurch($church->id)
            ->with(['member.branch', 'assignedLeader.member', 'responder', 'branch'])
            ->latest();

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($request->string('filter')->toString() === 'mine' && $user->member_id) {
            $leaderIds = \App\Models\Leader::forChurch($church->id)
                ->where('member_id', $user->member_id)
                ->pluck('id');
            $query->whereIn('assigned_leader_id', $leaderIds);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('member', fn ($m) => $m->where('full_name', 'like', "%{$search}%"));
            });
        }

        return view('church.member-requests.index', [
            'requests' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'filter', 'branch_id']),
            'statuses' => \App\Enums\MemberRequestStatus::cases(),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'canFilterBranches' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user),
        ]);
    }

    public function show(MemberRequest $memberRequest): View
    {
        $this->authorize('view', $memberRequest);

        $memberRequest->load(['member', 'assignedLeader.member', 'responder']);

        return view('church.member-requests.show', [
            'memberRequest' => $memberRequest,
            'statuses' => \App\Enums\MemberRequestStatus::cases(),
        ]);
    }

    public function respond(RespondMemberRequestRequest $request, MemberRequest $memberRequest): RedirectResponse
    {
        $this->authorize('respond', $memberRequest);

        $updated = $this->memberRequestService->respond(
            $memberRequest,
            auth()->user(),
            $request->validated('status'),
            $request->validated('response'),
        );

        $message = 'Request updated successfully.';

        if ($updated->hasDownloadableCertificate()) {
            $message .= ' The certificate has been generated and is ready for the member to download.';
        } elseif ($this->certificateService->isEligible($updated) && ! $updated->certificate_path) {
            return back()
                ->with('success', 'Request updated successfully.')
                ->with('warning', 'The request was approved, but the certificate could not be generated. '
                    .'Ensure the PHP GD extension is enabled (extension=gd in php.ini) and restart the server, then try downloading the certificate again.');
        }

        return back()->with('success', $message);
    }

    public function downloadCertificate(MemberRequest $memberRequest)
    {
        $this->authorize('downloadCertificate', $memberRequest);

        return $this->certificateService->download($memberRequest);
    }
}
