<?php

namespace App\Http\Controllers\Church;

use App\Enums\DependantRelationship;
use App\Enums\EducationLevel;
use App\Enums\MaritalStatus;
use App\Enums\MemberType;
use App\Enums\MembershipType;
use App\Enums\WeddingType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\ArchiveMemberRequest;
use App\Http\Requests\Church\ConvertMemberToPermanentRequest;
use App\Http\Requests\Church\ExtendTemporaryMembershipRequest;
use App\Http\Requests\Church\StoreMemberRequest;
use App\Http\Requests\Church\UpdateMemberRequest;
use App\Models\Member;
use App\Services\Church\MemberService;
use App\Services\Church\BranchAccessService;
use App\Services\Church\ChurchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $memberService,
        private readonly BranchAccessService $branchAccessService,
        private readonly ChurchContextService $churchContextService,
    ) {
        $this->authorizeResource(Member::class, 'member', [
            'except' => ['checkEnvelope', 'resetPassword', 'archive', 'restore', 'archived', 'convertToPermanent', 'extendMembership'],
        ]);
    }

    public function index(Request $request): View
    {
        $user = auth()->user();
        $church = $user->church;

        $query = Member::forChurch($church->id)
            ->activeMembers()
            ->with(['spouseMember', 'user', 'branch'])
            ->latest();

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%")
                    ->orWhere('envelope_number', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($membershipType = $request->string('membership_type')->trim()->toString()) {
            $query->where('membership_type', $membershipType);
        }

        $members = $query->paginate(15)->withQueryString();

        return view('church.members.index', [
            'members' => $members,
            'filters' => $request->only(['search', 'membership_type', 'branch_id']),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'canFilterBranches' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user),
            'branchesEnabled' => $this->branchAccessService->branchesFeatureEnabled($user),
            'registrationUrl' => $user->can('member_registrations.view') || $user->can('members.create')
                ? $this->churchContextService->registrationUrl($church)
                : null,
        ]);
    }

    public function create(): View
    {
        $user = auth()->user();
        $church = $user->church;

        $churchMembers = Member::forChurch($church->id)
            ->activeMembers()
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_number', 'envelope_number', 'gender', 'date_of_birth', 'phone_number', 'email']);

        return view('church.members.create', [
            'churchMembers' => $churchMembers,
            'branches' => $this->branchAccessService->selectableBranches($user),
            'defaultBranchId' => $this->branchAccessService->resolveBranchIdForCreate($user, null),
            'membershipTypes' => MembershipType::cases(),
            'memberTypes' => MemberType::cases(),
            'educationLevels' => EducationLevel::cases(),
            'maritalStatuses' => MaritalStatus::cases(),
            'weddingTypes' => WeddingType::cases(),
            'dependantRelationships' => DependantRelationship::cases(),
            'tribes' => config('tanzania.tribes'),
            'durationUnits' => \App\Enums\TemporaryDurationUnit::cases(),
            'registrationUrl' => $this->churchContextService->registrationUrl($church),
        ]);
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $church = auth()->user()->church;

        $member = $this->memberService->create(
            $church,
            $request->safe()->except(['profile_picture', 'dependants']),
            $request->file('profile_picture'),
            $request->input('dependants', [])
        );

        $message = $this->memberService->spouseMemberWasCreated()
            ? 'Member and spouse registered successfully. Both now appear in the members list.'
            : 'Member registered successfully.';

        $redirect = redirect()
            ->route('church.members.show', $member)
            ->with('success', $message);

        if (auth()->user()->canManageMemberPasswords()) {
            $redirect->with('registered_accounts', $this->memberService->getRegisteredAccounts());
        } elseif ($this->memberService->getRegisteredAccounts() !== []) {
            $redirect->with('info', 'Login account(s) created. Contact your church administrator for member credentials.');
        }

        return $redirect;
    }

    public function archived(Request $request): View
    {
        $this->authorize('viewAny', Member::class);

        $user = auth()->user();
        $church = $user->church;

        $query = Member::forChurch($church->id)
            ->archived()
            ->with(['user', 'branch', 'archivedBy'])
            ->latest('archived_at');

        $this->branchAccessService->applyBranchFilter(
            $query,
            $user,
            $request->integer('branch_id') ?: null,
        );

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%")
                    ->orWhere('envelope_number', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $members = $query->paginate(15)->withQueryString();

        return view('church.members.archived', [
            'members' => $members,
            'filters' => $request->only(['search', 'branch_id']),
            'branches' => $this->branchAccessService->selectableBranches($user),
            'canFilterBranches' => $this->branchAccessService->branchesFeatureEnabled($user)
                && $this->branchAccessService->managesAllBranches($user),
            'branchesEnabled' => $this->branchAccessService->branchesFeatureEnabled($user),
        ]);
    }

    public function show(Member $member): View
    {
        $member->load(['dependants', 'spouseMember', 'spouseOf', 'user', 'archivedBy']);

        return view('church.members.show', [
            'member' => $member,
            'familyDependants' => $member->familyDependants(),
            'memberTypes' => MemberType::cases(),
            'durationUnits' => \App\Enums\TemporaryDurationUnit::cases(),
        ]);
    }

    public function edit(Member $member): View
    {
        $user = auth()->user();
        $church = $user->church;
        $member->load(['spouseMember', 'branch', 'dependants']);

        $churchMembers = Member::forChurch($church->id)
            ->activeMembers()
            ->whereKeyNot($member->id)
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_number', 'envelope_number', 'gender', 'date_of_birth', 'phone_number', 'email']);

        return view('church.members.edit', [
            'member' => $member,
            'churchMembers' => $churchMembers,
            'branches' => $this->branchAccessService->selectableBranches($user),
            'defaultBranchId' => $member->branch_id,
            'membershipTypes' => MembershipType::cases(),
            'memberTypes' => MemberType::cases(),
            'educationLevels' => EducationLevel::cases(),
            'maritalStatuses' => MaritalStatus::cases(),
            'weddingTypes' => WeddingType::cases(),
            'tribes' => config('tanzania.tribes'),
            'durationUnits' => \App\Enums\TemporaryDurationUnit::cases(),
            'branchesEnabled' => $this->branchAccessService->branchesFeatureEnabled($user),
        ]);
    }

    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $updated = $this->memberService->update(
            $member,
            $request->safe()->except(['profile_picture']),
            $request->file('profile_picture'),
        );

        $message = $this->memberService->spouseMemberWasCreated()
            ? 'Member updated successfully. A spouse member record was also created and linked.'
            : 'Member updated successfully.';

        return redirect()
            ->route('church.members.show', $updated)
            ->with('success', $message);
    }

    public function resetPassword(Member $member): RedirectResponse
    {
        $this->authorize('resetPassword', $member);

        try {
            $password = $this->memberService->resetMemberPassword($member);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            "Password reset for {$member->full_name}. New password: {$password} (last name in capital letters). An SMS was sent to the member's phone if a number is on file."
        );
    }

    public function archive(ArchiveMemberRequest $request, Member $member): RedirectResponse
    {
        if ($member->isArchived()) {
            return back()->with('error', 'This member is already archived.');
        }

        $this->memberService->archive($member, $request->validated('archive_reason'));

        return redirect()
            ->route('church.members.archived')
            ->with('success', "{$member->full_name} has been archived.");
    }

    public function restore(Member $member): RedirectResponse
    {
        $this->authorize('restore', $member);

        try {
            $this->memberService->restore($member);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('church.members.show', $member)
            ->with('success', "{$member->full_name} has been restored and can log in again.");
    }

    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('delete', $member);

        $name = $member->full_name;
        $this->memberService->deleteMember($member);

        return redirect()
            ->route('church.members.index')
            ->with('success', "{$name} has been deleted.");
    }

    public function convertToPermanent(ConvertMemberToPermanentRequest $request, Member $member): RedirectResponse
    {
        try {
            $this->memberService->convertToPermanent(
                $member,
                MemberType::from($request->validated('member_type'))
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "{$member->full_name} is now a permanent member.");
    }

    public function extendMembership(ExtendTemporaryMembershipRequest $request, Member $member): RedirectResponse
    {
        try {
            $member = $this->memberService->extendTemporaryMembership(
                $member,
                (int) $request->validated('temporary_duration_value'),
                \App\Enums\TemporaryDurationUnit::from($request->validated('temporary_duration_unit'))
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            "Membership extended for {$member->full_name}. New expiry date: {$member->membership_expires_at->format('M d, Y')}."
        );
    }

    public function checkEnvelope(Request $request): JsonResponse
    {
        $exceptMemberId = $request->integer('except') ?: null;

        if ($exceptMemberId) {
            $member = Member::forChurch(auth()->user()->church_id)->findOrFail($exceptMemberId);
            $this->authorize('update', $member);
        } else {
            $this->authorize('create', Member::class);
        }

        $envelope = $request->string('envelope')->trim()->toString();

        if (strlen($envelope) !== 3 || ! ctype_digit($envelope)) {
            return response()->json(['available' => false, 'message' => 'Envelope number must be exactly 3 digits.']);
        }

        $available = $this->memberService->isEnvelopeAvailable(
            auth()->user()->church,
            $envelope,
            $exceptMemberId,
        );

        return response()->json([
            'available' => $available,
            'message' => $available ? 'Envelope number is available.' : 'Envelope number is already in use.',
        ]);
    }
}
