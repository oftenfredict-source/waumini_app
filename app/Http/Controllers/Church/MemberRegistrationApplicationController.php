<?php

namespace App\Http\Controllers\Church;

use App\Http\Controllers\Controller;
use App\Http\Requests\Church\ApproveMemberRegistrationRequest;
use App\Http\Requests\Church\RejectMemberRegistrationRequest;
use App\Models\MemberRegistrationApplication;
use App\Services\Church\BranchAccessService;
use App\Services\Church\ChurchContextService;
use App\Services\Church\MemberRegistrationApplicationService;
use App\Services\Church\MemberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberRegistrationApplicationController extends Controller
{
    public function __construct(
        private readonly MemberRegistrationApplicationService $registrationService,
        private readonly MemberService $memberService,
        private readonly BranchAccessService $branchAccessService,
        private readonly ChurchContextService $churchContextService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MemberRegistrationApplication::class);

        $church = auth()->user()->church;
        $user = auth()->user();

        $query = MemberRegistrationApplication::forChurch($church->id)
            ->with(['branch', 'reviewer', 'member'])
            ->latest();

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        return view('church.member-registrations.index', [
            'applications' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'branch_id']),
            'statuses' => \App\Enums\MemberRegistrationStatus::cases(),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'canFilterBranches' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user),
            'pendingCount' => MemberRegistrationApplication::forChurch($church->id)
                ->where('status', 'pending')
                ->count(),
            'registrationUrl' => $this->churchContextService->registrationUrl($church),
            'registrationSubdomainUrl' => $this->churchContextService->registrationSubdomainUrl($church),
        ]);
    }

    public function show(MemberRegistrationApplication $registration): View
    {
        $this->authorize('view', $registration);

        $registration->load(['branch', 'reviewer', 'member']);
        $data = $registration->registration_data ?? [];
        $needsSpouseEnvelope = ($data['marital_status'] ?? null) === 'married'
            && ($data['spouse_church_member'] ?? null) === 'yes'
            && empty($data['spouse_member_id']);

        return view('church.member-registrations.show', [
            'application' => $registration,
            'registrationData' => $data,
            'dependants' => $registration->dependants_data ?? [],
            'needsSpouseEnvelope' => $needsSpouseEnvelope,
        ]);
    }

    public function approve(ApproveMemberRegistrationRequest $request, MemberRegistrationApplication $registration): RedirectResponse
    {
        $result = $this->registrationService->approve(
            $registration,
            $request->user(),
            $request->string('envelope_number')->toString(),
            $request->string('spouse_envelope_number')->toString() ?: null,
        );

        $redirect = redirect()
            ->route('church.member-registrations.show', $registration)
            ->with('success', 'Registration approved. Member account created successfully.');

        if ($request->user()->canManageMemberPasswords()) {
            $redirect->with('registered_accounts', $result['accounts']);
        }

        return $redirect;
    }

    public function reject(RejectMemberRegistrationRequest $request, MemberRegistrationApplication $registration): RedirectResponse
    {
        $this->registrationService->reject(
            $registration,
            $request->user(),
            $request->string('rejection_reason')->trim()->toString() ?: null,
        );

        return redirect()
            ->route('church.member-registrations.show', $registration)
            ->with('success', 'Registration application rejected.');
    }

    public function checkEnvelope(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', MemberRegistrationApplication::class);

        $envelope = $request->string('envelope')->trim()->toString();
        $church = $request->user()->church;

        if (strlen($envelope) !== 3 || ! ctype_digit($envelope)) {
            return response()->json(['available' => false, 'message' => 'Envelope must be 3 digits.']);
        }

        $available = $this->memberService->isEnvelopeAvailable($church, $envelope);

        return response()->json([
            'available' => $available,
            'message' => $available ? 'Envelope number is available.' : 'Envelope number is already in use.',
        ]);
    }
}
