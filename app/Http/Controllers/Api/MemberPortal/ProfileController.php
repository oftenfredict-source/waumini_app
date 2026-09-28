<?php

namespace App\Http\Controllers\Api\MemberPortal;

use App\Http\Requests\Church\UpdateMemberPasswordRequest;
use App\Http\Requests\Church\UpdateMemberProfileRequest;
use App\Http\Resources\Api\MemberDependantResource;
use App\Http\Resources\Api\MemberDetailResource;
use App\Services\Church\MemberProfileService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProfileController extends MemberPortalController
{
    public function __construct(
        private readonly MemberProfileService $memberProfileService,
    ) {}

    public function show(): JsonResponse
    {
        return ApiResponse::success($this->profilePayload($this->member()));
    }

    public function update(UpdateMemberProfileRequest $request): JsonResponse
    {
        $member = $this->member();
        $this->authorize('updateOwnProfile', $member);

        $updated = $this->memberProfileService->updateProfile(
            $member,
            $request->validated(),
            $request->file('profile_picture'),
        );

        return ApiResponse::success($this->profilePayload($updated), 'Profile updated successfully.');
    }

    public function updatePassword(UpdateMemberPasswordRequest $request): JsonResponse
    {
        $member = $this->member();
        $this->authorize('updateOwnProfile', $member);

        $this->memberProfileService->updatePassword(
            request()->user(),
            $request->validated('current_password'),
            $request->validated('password'),
        );

        return ApiResponse::success(message: 'Password changed successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function profilePayload($member): array
    {
        $member->load(['church', 'departments', 'spouseMember', 'user', 'branch']);

        return [
            'member' => (new MemberDetailResource($member))->resolve(),
            'church' => [
                'id' => $member->church?->id,
                'name' => $member->church?->name,
                'currency' => $member->church?->currency ?? 'TZS',
            ],
            'departments' => $member->departments->map(fn ($department) => [
                'id' => $department->id,
                'name' => $department->name,
                'role' => $department->pivot->role ?? null,
            ])->values()->all(),
            'spouse' => $member->spouseMember ? [
                'id' => $member->spouseMember->id,
                'full_name' => $member->spouseMember->full_name,
                'member_number' => $member->spouseMember->member_number,
            ] : null,
            'family_dependants' => MemberDependantResource::collection($member->familyDependants())->resolve(),
        ];
    }
}
