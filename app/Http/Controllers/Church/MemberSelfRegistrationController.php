<?php

namespace App\Http\Controllers\Church;

use App\Enums\EducationLevel;
use App\Enums\MaritalStatus;
use App\Enums\MemberType;
use App\Enums\MembershipType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Church\StoreMemberSelfRegistrationRequest;
use App\Models\ChurchBranch;
use App\Models\Member;
use App\Services\Church\ChurchContextService;
use App\Services\Church\MemberRegistrationApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MemberSelfRegistrationController extends Controller
{
    public function __construct(
        private readonly ChurchContextService $churchContextService,
        private readonly MemberRegistrationApplicationService $registrationService,
    ) {}

    public function create(): View|RedirectResponse
    {
        $church = $this->churchContextService->resolveFromRequest(request());

        if (! $church) {
            return view('church.auth.register-unavailable');
        }

        $this->churchContextService->bindCurrentChurch($church);

        $branches = $church->branches_enabled
            ? $church->branches()->orderBy('name')->get()
            : collect();

        $churchMembers = Member::forChurch($church->id)
            ->where('status', 'active')
            ->with(['spouseMember:id,full_name,member_number', 'spouseOf:id,full_name,member_number,spouse_member_id'])
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'member_number', 'envelope_number', 'gender', 'date_of_birth', 'phone_number', 'email', 'spouse_member_id']);

        return view('church.auth.register', [
            'church' => $church,
            'branches' => $branches,
            'defaultBranchId' => $church->headquarters?->id ?? $branches->first()?->id,
            'membershipTypes' => MembershipType::cases(),
            'memberTypes' => MemberType::cases(),
            'educationLevels' => EducationLevel::cases(),
            'maritalStatuses' => MaritalStatus::cases(),
            'weddingTypes' => \App\Enums\WeddingType::cases(),
            'dependantRelationships' => \App\Enums\DependantRelationship::cases(),
            'tribes' => config('tanzania.tribes'),
            'durationUnits' => \App\Enums\TemporaryDurationUnit::cases(),
            'churchMembers' => $churchMembers,
        ]);
    }

    public function store(StoreMemberSelfRegistrationRequest $request): RedirectResponse
    {
        $church = $request->church();

        abort_unless($church, 404);

        $this->churchContextService->bindCurrentChurch($church);

        $application = $this->registrationService->submit(
            $church,
            $request->safe()->except(['profile_picture', 'dependants']),
            $request->file('profile_picture'),
            $request->input('dependants', []),
        );

        return redirect()
            ->route('church.register.success', ['reference' => $application->application_number])
            ->with('success', 'Your registration has been submitted and is awaiting church approval.');
    }

    public function success(string $reference): View
    {
        return view('church.auth.register-success', [
            'reference' => $reference,
        ]);
    }
}
