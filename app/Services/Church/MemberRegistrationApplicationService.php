<?php

namespace App\Services\Church;

use App\Enums\MemberRegistrationStatus;
use App\Models\Church;
use App\Models\Member;
use App\Models\MemberRegistrationApplication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MemberRegistrationApplicationService
{
    public function __construct(
        private readonly MemberService $memberService,
    ) {}

    public function submit(
        Church $church,
        array $data,
        ?UploadedFile $profilePicture = null,
        array $dependants = [],
    ): MemberRegistrationApplication {
        $data['spouse_input_method'] = 'manual';
        $data['spouse_member_id'] = null;

        if (($data['spouse_church_member'] ?? null) === 'yes') {
            $data['spouse_envelope_number'] = null;
        }

        $profilePath = null;

        if ($profilePicture) {
            $profilePath = $profilePicture->store("churches/{$church->id}/registration-applications", 'public');
        }

        $phone = $this->memberService->normalizePhoneNumber($data['phone_number'] ?? null);

        return MemberRegistrationApplication::create([
            'church_id' => $church->id,
            'branch_id' => $data['branch_id'] ?? null,
            'application_number' => $this->nextApplicationNumber($church->id),
            'full_name' => $data['full_name'],
            'phone_number' => $phone,
            'registration_data' => $data,
            'dependants_data' => $dependants,
            'profile_picture_path' => $profilePath,
            'status' => MemberRegistrationStatus::Pending,
        ]);
    }

    /**
     * @return array{member: Member, accounts: array<int, array{name: string, member_id: string, password: string}>}
     */
    public function approve(
        MemberRegistrationApplication $application,
        User $reviewer,
        string $envelopeNumber,
        ?string $spouseEnvelopeNumber = null,
    ): array {
        if (! $application->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'This registration has already been reviewed.',
            ]);
        }

        if (! $this->memberService->isEnvelopeAvailable($application->church, $envelopeNumber)) {
            throw ValidationException::withMessages([
                'envelope_number' => 'This envelope number is already in use.',
            ]);
        }

        return DB::transaction(function () use ($application, $reviewer, $envelopeNumber, $spouseEnvelopeNumber) {
            $data = $application->registration_data;
            $data['envelope_number'] = $envelopeNumber;
            $data['branch_id'] = $data['branch_id'] ?? $application->branch_id;

            if (! empty($data['spouse_church_member']) && $data['spouse_church_member'] === 'yes' && $spouseEnvelopeNumber) {
                if (! $this->memberService->isEnvelopeAvailable($application->church, $spouseEnvelopeNumber, null)) {
                    throw ValidationException::withMessages([
                        'spouse_envelope_number' => 'Spouse envelope number is already in use.',
                    ]);
                }

                $data['spouse_envelope_number'] = $spouseEnvelopeNumber;
            }

            $profilePicture = null;

            if ($application->profile_picture_path) {
                $data['profile_picture'] = $application->profile_picture_path;
            }

            $member = $this->memberService->create(
                $application->church,
                $data,
                $profilePicture,
                $application->dependants_data ?? [],
            );

            $application->update([
                'status' => MemberRegistrationStatus::Approved,
                'assigned_envelope_number' => $envelopeNumber,
                'member_id' => $member->id,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            return [
                'member' => $member,
                'accounts' => $this->memberService->getRegisteredAccounts(),
            ];
        });
    }

    public function reject(
        MemberRegistrationApplication $application,
        User $reviewer,
        ?string $reason = null,
    ): MemberRegistrationApplication {
        if (! $application->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'This registration has already been reviewed.',
            ]);
        }

        $application->update([
            'status' => MemberRegistrationStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $application->fresh();
    }

    private function nextApplicationNumber(int $churchId): string
    {
        $year = now()->year;
        $prefix = "REG-{$year}-";
        $latest = MemberRegistrationApplication::forChurch($churchId)
            ->where('application_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('application_number');

        $sequence = 1;

        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
