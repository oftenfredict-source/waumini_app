<?php

namespace App\Http\Controllers\Church\MemberPortal;

use App\Http\Requests\Church\StoreMemberRequestRequest;
use App\Models\MemberRequest;
use App\Services\Church\MemberRequestService;
use App\Services\Church\MemberRequestCertificateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RequestController extends MemberPortalController
{
    public function __construct(
        private readonly MemberRequestService $memberRequestService,
        private readonly MemberRequestCertificateService $certificateService,
    ) {}

    public function index(): View
    {
        $member = $this->member();

        $requests = MemberRequest::forChurch($member->church_id)
            ->where('member_id', $member->id)
            ->with(['assignedLeader.member', 'responder'])
            ->latest()
            ->paginate(15);

        return view('church.member-portal.requests.index', [
            'requests' => $requests,
            'church' => $member->church,
        ]);
    }

    public function create(): View
    {
        $member = $this->member();
        $leaders = $this->memberRequestService->assignableLeaders($member->church, $member->branch_id);

        $children = $member->familyDependants()
            ->filter(fn ($dependant) => $dependant->relationship === \App\Enums\DependantRelationship::Child);

        return view('church.member-portal.requests.create', [
            'leaders' => $leaders,
            'types' => \App\Enums\MemberRequestType::cases(),
            'church' => $member->church,
            'children' => $children,
            'member' => $member,
        ]);
    }

    public function store(StoreMemberRequestRequest $request): RedirectResponse
    {
        $member = $this->member();

        $leader = \App\Models\Leader::query()->findOrFail($request->validated('assigned_leader_id'));
        abort_unless($leader->church_id === $member->church_id, 403);

        $memberRequest = $this->memberRequestService->create($member, $request->validated());

        return redirect()
            ->route('church.member.requests.show', $memberRequest)
            ->with('success', "Request submitted successfully. Reference: {$memberRequest->reference_number}");
    }

    public function show(MemberRequest $memberRequest): View
    {
        $this->authorize('view', $memberRequest);

        $memberRequest->load(['assignedLeader.member', 'responder', 'member', 'church']);

        return view('church.member-portal.requests.show', [
            'memberRequest' => $memberRequest,
            'church' => $memberRequest->church,
        ]);
    }

    public function downloadCertificate(MemberRequest $memberRequest)
    {
        $this->authorize('downloadCertificate', $memberRequest);

        return $this->certificateService->download($memberRequest);
    }
}
