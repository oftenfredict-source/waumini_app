<?php

namespace App\Http\Controllers\Church\MemberPortal;

use App\Http\Requests\Church\UpdateMemberPasswordRequest;
use App\Http\Requests\Church\UpdateMemberProfileRequest;
use App\Services\Church\MemberProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends MemberPortalController
{
    public function __construct(
        private readonly MemberProfileService $memberProfileService,
    ) {}

    public function edit(): View
    {
        $member = $this->member()->load(['church', 'departments', 'spouseMember', 'user']);

        return view('church.member-portal.profile.edit', [
            'member' => $member,
            'church' => $member->church,
            'familyDependants' => $member->familyDependants(),
        ]);
    }

    public function update(UpdateMemberProfileRequest $request): RedirectResponse
    {
        $member = $this->member();

        $this->authorize('updateOwnProfile', $member);

        $this->memberProfileService->updateProfile(
            $member,
            $request->validated(),
            $request->file('profile_picture'),
        );

        return redirect()
            ->route('church.member.profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(UpdateMemberPasswordRequest $request): RedirectResponse
    {
        $member = $this->member();

        $this->authorize('updateOwnProfile', $member);

        $this->memberProfileService->updatePassword(
            auth()->user(),
            $request->validated('current_password'),
            $request->validated('password'),
        );

        return redirect()
            ->route('church.member.profile.edit')
            ->with('success', 'Password changed successfully.');
    }
}
