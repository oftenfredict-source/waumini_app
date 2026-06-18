<?php

namespace App\Services\Church;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MemberProfileService
{
    public function __construct(
        private readonly MemberService $memberService,
    ) {}

    /**
     * @param  array{phone_number?: string|null}  $data
     */
    public function updateProfile(Member $member, array $data, ?UploadedFile $profilePicture = null): Member
    {
        $updates = [];

        if (array_key_exists('phone_number', $data)) {
            $updates['phone_number'] = $this->memberService->normalizePhoneNumber($data['phone_number'] ?? null);
        }

        if ($profilePicture) {
            if ($member->profile_picture) {
                Storage::disk('public')->delete($member->profile_picture);
            }

            $updates['profile_picture'] = $profilePicture->store("churches/{$member->church_id}/members", 'public');
        }

        if ($updates !== []) {
            $member->update($updates);
        }

        if ($member->user && isset($updates['phone_number'])) {
            $member->user->update(['phone' => $updates['phone_number']]);
        }

        return $member->fresh(['user', 'church', 'departments']);
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        $user->update(['password' => $newPassword]);
    }
}
