<?php

namespace App\Http\Controllers\Church;

use App\Enums\MemberRegistrationStatus;
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

        // This page is for approvals: show pending by default so approved ones leave the list.
        $status = $request->string('status')->trim()->toString();
        if ($status === '') {
            $status = MemberRegistrationStatus::Pending->value;
        }
        $query->where('status', $status);

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $registrationBranch = $this->branchAccessService->activeBranch($user);

        return view('church.member-registrations.index', [
            'applications' => $query->paginate(15)->withQueryString(),
            'filters' => array_merge($request->only(['search', 'branch_id']), ['status' => $status]),
            'statuses' => MemberRegistrationStatus::cases(),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'canFilterBranches' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user)
                && ! $this->branchAccessService->sessionBranchId($user),
            'pendingCount' => MemberRegistrationApplication::forChurch($church->id)
                ->where('status', MemberRegistrationStatus::Pending)
                ->count(),
            'registrationUrl' => $this->churchContextService->registrationUrl($church, $registrationBranch),
            'registrationSubdomainUrl' => $this->churchContextService->registrationSubdomainUrl($church, $registrationBranch),
            'registrationBranch' => $registrationBranch,
        ]);
    }

    public function show(MemberRegistrationApplication $registration): View
    {
        $this->authorize('view', $registration);

        $registration->load(['branch', 'reviewer', 'member', 'church']);
        $data = $registration->registration_data ?? [];
        $needsSpouseEnvelope = MemberRegistrationApplicationService::needsSpouseEnvelope($data);
        $settings = app(\App\Services\Church\ChurchSettingsService::class);
        $church = $registration->church;
        $applicantAge = null;
        if (! empty($data['date_of_birth'])) {
            try {
                $applicantAge = \Carbon\Carbon::parse($data['date_of_birth'])->age;
            } catch (\Throwable) {
                $applicantAge = null;
            }
        }
        $spouseAge = null;
        if (! empty($data['spouse_date_of_birth'])) {
            try {
                $spouseAge = \Carbon\Carbon::parse($data['spouse_date_of_birth'])->age;
            } catch (\Throwable) {
                $spouseAge = null;
            }
        }

        $matchingDependant = null;
        $matchingMember = null;

        if ($registration->isPending()) {
            $matchingMember = $this->memberService->findMatchingMember($registration->church, $data);
            $matchingDependant = $this->memberService->findMatchingUnconvertedDependant($registration->church, $data);
        }

        return view('church.member-registrations.show', [
            'application' => $registration,
            'registrationData' => $data,
            'dependants' => $registration->dependants_data ?? [],
            'needsSpouseEnvelope' => $needsSpouseEnvelope,
            'envelopeRequired' => $settings->envelopeRequiredForAge($church, $applicantAge),
            'spouseEnvelopeRequired' => $needsSpouseEnvelope && $settings->envelopeRequiredForAge($church, $spouseAge),
            'envelopeRequiredFromAge' => $settings->envelopeRequiredFromAge($church),
            'youthMaxAge' => $settings->youthMaxAge($church),
            'matchingDependant' => $matchingDependant,
            'matchingMember' => $matchingMember,
        ]);
    }

    public function approve(ApproveMemberRegistrationRequest $request, MemberRegistrationApplication $registration): RedirectResponse
    {
        $result = $this->registrationService->approve(
            $registration,
            $request->user(),
            $request->filled('envelope_number') ? $request->string('envelope_number')->toString() : null,
            $request->filled('spouse_envelope_number') ? $request->string('spouse_envelope_number')->toString() : null,
        );

        $message = $this->memberService->spouseMemberWasCreated()
            ? 'Registration approved. Member and spouse accounts were created successfully.'
            : 'Registration approved. Member account created successfully.';

        if ($linked = $this->memberService->linkedExistingDependant()) {
            $parentName = $linked->guardianDisplayName();
            $message = "Registration approved. Linked to the existing child record under {$parentName} — no duplicate person was created.";
        }

        $redirect = redirect()
            ->route('church.member-registrations.index')
            ->with('success', $message);

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
            ->route('church.member-registrations.index')
            ->with('success', 'Registration application rejected.');
    }

    public function checkEnvelope(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize('viewAny', MemberRegistrationApplication::class);

        $envelope = $request->string('envelope')->trim()->toString();
        $church = $request->user()->church;
        $branchId = $request->integer('branch_id') ?: null;

        if ($registrationId = $request->integer('registration') ?: null) {
            $application = MemberRegistrationApplication::forChurch($church->id)->find($registrationId);
            $branchId = $application?->branch_id
                ?? ($application?->registration_data['branch_id'] ?? $branchId);
        }

        if (strlen($envelope) !== 3 || ! ctype_digit($envelope)) {
            return response()->json(['available' => false, 'message' => 'Envelope must be 3 digits.']);
        }

        $available = $this->memberService->isEnvelopeAvailable($church, $envelope, null, $branchId ? (int) $branchId : null);

        return response()->json([
            'available' => $available,
            'message' => $available ? 'Envelope number is available.' : 'Envelope number is already in use in this branch.',
        ]);
    }
}
