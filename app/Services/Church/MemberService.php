<?php

namespace App\Services\Church;

use App\Enums\DependantRelationship;
use App\Enums\MaritalStatus;
use App\Enums\MemberStatus;
use App\Enums\MemberType;
use App\Enums\MembershipType;
use App\Enums\TemporaryDurationUnit;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\Church;
use App\Models\ChurchBranch;
use App\Models\Member;
use App\Models\MemberDependant;
use App\Models\User;
use App\Services\Church\CelebrationService;
use App\Services\Church\ChurchSettingsService;
use App\Services\Sms\ChurchSmsService;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class MemberService
{
    private bool $spouseMemberCreated = false;

    private ?MemberDependant $linkedExistingDependant = null;

    /** @var array<int, array{name: string, member_id: string, password: string}> */
    private array $registeredAccounts = [];

    public function __construct(
        private readonly ChurchSmsService $churchSmsService,
        private readonly CelebrationService $celebrationService,
        private readonly DepartmentAssignmentService $departmentAssignmentService,
    ) {}

    public function create(Church $church, array $data, ?UploadedFile $profilePicture = null, array $dependants = []): Member
    {
        $this->spouseMemberCreated = false;
        $this->linkedExistingDependant = null;
        $this->registeredAccounts = [];

        return DB::transaction(function () use ($church, $data, $profilePicture, $dependants) {
            $this->assertNotAlreadyRegisteredMember($church, $data);

            $matchingDependant = $this->findMatchingUnconvertedDependant($church, $data);
            $data = $this->applyExistingDependantFamilyLink($data, $matchingDependant);

            $spouseInputMethod = $data['spouse_input_method'] ?? null;
            $selectedSpouseMemberId = $data['spouse_member_id'] ?? null;

            $data['church_id'] = $church->id;
            $data['branch_id'] = $this->resolveBranchId($church, $data['branch_id'] ?? null);
            $data['member_number'] = $this->generateMemberId($church);
            $data['status'] = $data['status'] ?? MemberStatus::Active;
            $data['membership_date'] = $data['membership_date'] ?? now()->toDateString();
            $data['phone_number'] = $this->normalizePhoneNumber($data['phone_number'] ?? null);
            $data = $this->applyMembershipDuration($data);

            if ($profilePicture) {
                $data['profile_picture'] = $profilePicture->store("churches/{$church->id}/members", 'public');
            }

            if (($data['marital_status'] ?? null) !== MaritalStatus::Married->value) {
                $data = $this->clearSpouseFields($data);
                $data['wedding_type'] = null;
                $data['wedding_date'] = null;
            } elseif (! empty($selectedSpouseMemberId) && $spouseInputMethod === 'select') {
                $spouse = Member::forChurch($church->id)->find($selectedSpouseMemberId);
                if ($spouse) {
                    $data['spouse_full_name'] = $data['spouse_full_name'] ?? $spouse->full_name;
                    $data['spouse_gender'] = $data['spouse_gender'] ?? $spouse->gender;
                    $data['spouse_date_of_birth'] = $data['spouse_date_of_birth'] ?? $spouse->date_of_birth?->toDateString();
                    $data['spouse_phone_number'] = $data['spouse_phone_number'] ?? $spouse->phone_number;
                    $data['spouse_email'] = $data['spouse_email'] ?? $spouse->email;
                    $data['spouse_envelope_number'] = $data['spouse_envelope_number'] ?? $spouse->envelope_number;
                }
            }

            if (($data['spouse_church_member'] ?? null) !== 'yes') {
                $data['spouse_member_id'] = null;

                if (empty(trim((string) ($data['spouse_full_name'] ?? '')))) {
                    $data['spouse_envelope_number'] = null;
                }
            } elseif ($spouseInputMethod !== 'select') {
                $data['spouse_member_id'] = null;
            }

            $data['spouse_phone_number'] = $this->normalizePhoneNumber($data['spouse_phone_number'] ?? null);
            $data = $this->normalizeBaptismFields($data);
            $settings = app(ChurchSettingsService::class);
            $memberAge = $this->ageFromDateString($data['date_of_birth'] ?? null);
            $kipaimaraAllowed = $settings->canShowKipaimara($church, $memberAge, false);
            $data = $this->normalizeKipaimaraFields($data, $kipaimaraAllowed, forceClear: ! $kipaimaraAllowed);
            $data = $this->normalizeFamilyLink($data);

            unset($data['spouse_input_method'], $data['dependants'], $data['family_parent_type']);

            try {
                $member = Member::create($data);
            } catch (UniqueConstraintViolationException $e) {
                throw ValidationException::withMessages([
                    'envelope_number' => 'This envelope number is already in use in this branch.',
                ]);
            }
            $this->createMemberUserAccount($church, $member);

            if ($matchingDependant) {
                $this->linkDependantToMember($matchingDependant, $member);
                $this->linkedExistingDependant = $matchingDependant->fresh(['member']);
            }

            $spouseMember = $this->provisionSpouseMember(
                $church,
                $member,
                $spouseInputMethod,
                $selectedSpouseMemberId ? (int) $selectedSpouseMemberId : null,
            );

            if ($spouseMember && ! $spouseMember->user) {
                $this->createMemberUserAccount($church, $spouseMember->fresh());
            }

            if ($spouseMember) {
                $this->celebrationService->syncMember($spouseMember->fresh());
            }

            foreach ($dependants as $dependant) {
                $this->createDependantForMember($church, $member, $dependant);
            }

            $this->celebrationService->syncMember($member);

            $this->departmentAssignmentService->assignIfApplicable($church, $member);

            if ($this->spouseMemberCreated && $spouseMember) {
                $this->departmentAssignmentService->assignIfApplicable($church, $spouseMember->fresh());
            }

            return $member->fresh(['dependants', 'spouseMember', 'user']);
        });
    }

    public function update(Member $member, array $data, ?UploadedFile $profilePicture = null): Member
    {
        $this->spouseMemberCreated = false;

        return DB::transaction(function () use ($member, $data, $profilePicture) {
            $spouseInputMethod = $data['spouse_input_method'] ?? null;
            $selectedSpouseMemberId = $data['spouse_member_id'] ?? null;
            $hadLinkedSpouse = $member->spouse_member_id !== null;

            unset($data['member_number'], $data['church_id'], $data['spouse_input_method'], $data['dependants'], $data['family_parent_type']);

            $church = $member->church;

            if (($data['marital_status'] ?? null) !== MaritalStatus::Married->value) {
                $data = $this->clearSpouseFields($data);
                $data['wedding_type'] = null;
                $data['wedding_date'] = null;
            } elseif (! $hadLinkedSpouse) {
                if (! empty($selectedSpouseMemberId) && $spouseInputMethod === 'select') {
                    $spouse = Member::forChurch($church->id)->find($selectedSpouseMemberId);
                    if ($spouse) {
                        $data['spouse_full_name'] = $data['spouse_full_name'] ?? $spouse->full_name;
                        $data['spouse_gender'] = $data['spouse_gender'] ?? $spouse->gender;
                        $data['spouse_date_of_birth'] = $data['spouse_date_of_birth'] ?? $spouse->date_of_birth?->toDateString();
                        $data['spouse_phone_number'] = $data['spouse_phone_number'] ?? $spouse->phone_number;
                        $data['spouse_email'] = $data['spouse_email'] ?? $spouse->email;
                        $data['spouse_envelope_number'] = $data['spouse_envelope_number'] ?? $spouse->envelope_number;
                    }
                }

                if (($data['spouse_church_member'] ?? null) !== 'yes') {
                    $data['spouse_member_id'] = null;

                    if (empty(trim((string) ($data['spouse_full_name'] ?? '')))) {
                        $data['spouse_envelope_number'] = null;
                    }
                } elseif ($spouseInputMethod !== 'select') {
                    $data['spouse_member_id'] = null;
                }

                $data['spouse_phone_number'] = $this->normalizePhoneNumber($data['spouse_phone_number'] ?? null);
            }

            $data['phone_number'] = $this->normalizePhoneNumber($data['phone_number'] ?? null);
            $data = $this->normalizeBaptismFields($data);
            $settings = app(ChurchSettingsService::class);
            $memberAge = $this->ageFromDateString($data['date_of_birth'] ?? $member->date_of_birth?->toDateString());
            $kipaimaraAllowed = $settings->canShowKipaimara($church, $memberAge, (bool) $member->is_kipaimara);
            $data = $this->normalizeKipaimaraFields($data, $kipaimaraAllowed, forceClear: ! $kipaimaraAllowed && $memberAge !== null && $memberAge < $settings->kipaimaraMinAge($church));
            $data = $this->normalizeFamilyLink($data);

            if (array_key_exists('branch_id', $data)) {
                $data['branch_id'] = $this->resolveBranchId($church, $data['branch_id'] ?? $member->branch_id);
            }

            if ($profilePicture) {
                $data['profile_picture'] = $profilePicture->store("churches/{$church->id}/members", 'public');
            }

            if (($data['membership_type'] ?? $member->membership_type?->value) === MembershipType::Permanent->value) {
                $data['temporary_duration_value'] = null;
                $data['temporary_duration_unit'] = null;
            }

            // Linked spouses are edited on their own member record — never clear
            // spouse_* columns from a partial edit payload.
            if ($hadLinkedSpouse) {
                foreach (array_keys($data) as $key) {
                    if (str_starts_with($key, 'spouse_')) {
                        unset($data[$key]);
                    }
                }
            }

            $previousEnvelope = $member->envelope_number;

            try {
                $member->update($data);
            } catch (UniqueConstraintViolationException $e) {
                throw ValidationException::withMessages([
                    'envelope_number' => 'This envelope number is already in use in this branch.',
                ]);
            }

            $member = $member->fresh();

            if (
                $hadLinkedSpouse
                && $member->spouse_member_id
                && array_key_exists('envelope_number', $data)
                && $member->envelope_number !== $previousEnvelope
            ) {
                Member::forChurch($church->id)
                    ->whereKey($member->spouse_member_id)
                    ->update(['spouse_envelope_number' => $member->envelope_number]);
            }

            $spouseMember = null;

            if (! $hadLinkedSpouse) {
                $spouseMember = $this->provisionSpouseMember(
                    $church,
                    $member,
                    $spouseInputMethod,
                    $selectedSpouseMemberId ? (int) $selectedSpouseMemberId : null,
                );
            }

            if ($spouseMember && ! $spouseMember->user) {
                $this->createMemberUserAccount($church, $spouseMember->fresh());
            }

            if ($spouseMember) {
                $this->celebrationService->syncMember($spouseMember->fresh());
            }

            $this->celebrationService->syncMember($member->fresh());

            return $member->fresh(['spouseMember', 'branch', 'dependants']);
        });
    }

    public function addChild(Church $church, array $data, ?Member $parent = null): MemberDependant
    {
        $existingDependant = $this->findMatchingUnconvertedDependant($church, $data);

        if ($existingDependant) {
            if ($parent && (int) $existingDependant->member_id === (int) $parent->id) {
                throw ValidationException::withMessages([
                    'full_name' => 'This child is already registered under this parent.',
                ]);
            }

            throw ValidationException::withMessages([
                'full_name' => 'This child is already registered under '.$existingDependant->guardianDisplayName().'. One person cannot be registered twice.',
            ]);
        }

        $existingMember = $this->findMatchingMember($church, $data);
        $guardianPhone = $data['guardian_phone'] ?? null;
        if ($guardianPhone) {
            $guardianPhone = $this->normalizePhoneNumber($guardianPhone) ?: $guardianPhone;
        }

        $child = MemberDependant::create([
            'church_id' => $church->id,
            'member_id' => $parent?->id,
            'guardian_full_name' => $parent ? null : ($data['guardian_full_name'] ?? null),
            'guardian_phone' => $parent ? null : $guardianPhone,
            'guardian_relationship' => $parent ? null : ($data['guardian_relationship'] ?? null),
            'full_name' => $data['full_name'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'relationship' => DependantRelationship::Child,
            'relationship_note' => $data['relationship_note'] ?? null,
            'linked_member_id' => $existingMember?->id,
            ...$this->normalizeDependantEducationFields(
                $data,
                (bool) app(ChurchSettingsService::class)->get($church, 'children_education_details_enabled', false)
            ),
        ]);

        if (! $existingMember) {
            $this->departmentAssignmentService->assignDependantIfApplicable($church, $child);
        }

        return $child;
    }

    public function updateDependant(MemberDependant $dependant, array $data): MemberDependant
    {
        if ($dependant->isConverted()) {
            throw new \RuntimeException('This dependant is already an independent member. Edit them from the member profile.');
        }

        $church = $dependant->church ?? $dependant->member?->church;
        $allowKipaimara = (bool) $dependant->is_kipaimara;
        $allowEducation = (bool) $dependant->is_student || (bool) $dependant->education_level;

        if ($church) {
            $settings = app(ChurchSettingsService::class);
            $childAge = $this->ageFromDateString($data['date_of_birth'] ?? $dependant->date_of_birth?->toDateString());
            $allowKipaimara = $settings->canShowKipaimara($church, $childAge, (bool) $dependant->is_kipaimara);
            $allowEducation = $allowEducation
                || (bool) $settings->get($church, 'children_education_details_enabled', false);
        }

        $dependant->update([
            'full_name' => $data['full_name'],
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'relationship_note' => $data['relationship_note'] ?? null,
            ...$this->normalizeDependantBaptismFields($data),
            ...$this->normalizeDependantKipaimaraFields($data, $allowKipaimara),
            ...$this->normalizeDependantEducationFields($data, $allowEducation),
        ]);

        $dependant = $dependant->fresh(['member', 'linkedMember', 'departments']);
        $church = $dependant->church ?? $dependant->member?->church;

        if ($church) {
            $this->departmentAssignmentService->refreshDependantAssignment($church, $dependant);
        }

        return $dependant->fresh(['member', 'linkedMember']);
    }

    public function spouseMemberWasCreated(): bool
    {
        return $this->spouseMemberCreated;
    }

    public function linkedExistingDependant(): ?MemberDependant
    {
        return $this->linkedExistingDependant;
    }

    /**
     * @return array<int, array{name: string, member_id: string, password: string}>
     */
    public function getRegisteredAccounts(): array
    {
        return $this->registeredAccounts;
    }

    public function isEnvelopeAvailable(
        Church $church,
        ?string $envelope,
        ?int $exceptMemberId = null,
        ?int $branchId = null,
    ): bool {
        if ($envelope === null || $envelope === '') {
            return true;
        }

        if (strlen($envelope) !== 3 || ! ctype_digit($envelope)) {
            return false;
        }

        $exceptIds = [];

        if ($exceptMemberId) {
            $exceptIds[] = $exceptMemberId;
            $linkedSpouseId = Member::forChurch($church->id)->whereKey($exceptMemberId)->value('spouse_member_id');

            if ($linkedSpouseId) {
                $exceptIds[] = (int) $linkedSpouseId;
            }
        }

        // Only active members' assigned envelope_number blocks reuse.
        // Soft-deleted members are excluded by SoftDeletes. spouse_envelope_number
        // is cleaned up on delete so it cannot keep a freed number reserved.
        $query = Member::forChurch($church->id)
            ->where(function ($q) use ($envelope) {
                $q->where('envelope_number', $envelope)
                    ->orWhere('spouse_envelope_number', $envelope);
            });

        if ($church->branches_enabled) {
            $query->where('branch_id', $branchId);
        }

        if ($exceptIds !== []) {
            $query->whereKeyNot($exceptIds);
        }

        return ! $query->exists();
    }

    public function convertChildToIndependentMember(
        MemberDependant $dependant,
        ?string $envelopeNumber = null,
        ?string $phoneNumber = null
    ): Member {
        $this->registeredAccounts = [];

        return DB::transaction(function () use ($dependant, $envelopeNumber, $phoneNumber) {
            $dependant->load(['member', 'church']);
            $parent = $dependant->member;
            $church = $parent?->church ?? $dependant->church;

            if (! $church) {
                throw new \RuntimeException('Church not found for this child record.');
            }

            $envelopeNumber = $envelopeNumber !== null && $envelopeNumber !== ''
                ? $envelopeNumber
                : null;
            $settings = app(ChurchSettingsService::class);

            if ($settings->envelopeRequiredForAge($church, $dependant->age()) && ! $envelopeNumber) {
                throw new \RuntimeException(
                    'Envelope number is required from age '.$settings->envelopeRequiredFromAge($church).'.'
                );
            }

            if ($dependant->relationship !== DependantRelationship::Child) {
                throw new \RuntimeException('Only children can be converted to independent members.');
            }

            if ($dependant->isConverted()) {
                throw new \RuntimeException('This child has already been converted to an independent member.');
            }

            $existingMember = $this->findMatchingMember($church, [
                'full_name' => $dependant->full_name,
                'date_of_birth' => $dependant->date_of_birth?->toDateString(),
                'gender' => $dependant->gender,
            ]);

            if ($existingMember) {
                $this->linkDependantToMember($dependant, $existingMember);

                return $existingMember->fresh(['user']);
            }

            $graduationAge = app(ChurchSettingsService::class)->childGraduationAge($church);

            if (! $dependant->isEligibleForIndependence($graduationAge)) {
                throw new \RuntimeException(
                    "Child must be at least {$graduationAge} years old with a date of birth on file."
                );
            }

            if ($envelopeNumber && ! $this->isEnvelopeAvailable($church, $envelopeNumber, null, $parent?->branch_id)) {
                throw new \RuntimeException('Envelope number is already in use in this branch.');
            }

            $phone = $phoneNumber
                ? $this->normalizePhoneNumber($phoneNumber)
                : $this->normalizePhoneNumber($parent?->phone_number ?? $dependant->guardian_phone);

            $memberData = [
                'church_id' => $church->id,
                'branch_id' => $parent?->branch_id,
                'member_number' => $this->generateMemberId($church),
                'envelope_number' => $envelopeNumber,
                'member_type' => MemberType::Independent,
                'membership_type' => MembershipType::Permanent,
                'full_name' => $dependant->full_name,
                'gender' => $dependant->gender,
                'date_of_birth' => $dependant->date_of_birth,
                'phone_number' => $phone,
                'marital_status' => MaritalStatus::Single,
                'membership_date' => now()->toDateString(),
                'status' => MemberStatus::Active,
            ];

            if ($parent) {
                $memberData = array_merge($memberData, [
                    'family_member_id' => $parent->id,
                    'secondary_family_member_id' => $this->resolveSecondaryFamilyMemberId((int) $parent->id),
                    'guardian_relationship' => 'Child',
                    'region' => $parent->region,
                    'district' => $parent->district,
                    'ward' => $parent->ward,
                    'street' => $parent->street,
                    'po_box' => $parent->po_box,
                    'tribe' => $parent->tribe,
                    'other_tribe' => $parent->other_tribe,
                    'residence_region' => $parent->residence_region,
                    'residence_district' => $parent->residence_district,
                    'residence_ward' => $parent->residence_ward,
                    'residence_street' => $parent->residence_street,
                    'residence_road' => $parent->residence_road,
                    'residence_house_number' => $parent->residence_house_number,
                ]);
            }

            try {
                $member = Member::create($memberData);
            } catch (UniqueConstraintViolationException $e) {
                throw ValidationException::withMessages([
                    'envelope_number' => 'This envelope number is already in use in this branch.',
                ]);
            }

            $this->linkDependantToMember($dependant, $member);

            $this->createMemberUserAccount($church, $member);
            $this->departmentAssignmentService->assignIfApplicable($church, $member);

            return $member->fresh(['user']);
        });
    }

    public function processAgedOutChildren(Church $church): int
    {
        $graduationAge = app(ChurchSettingsService::class)->childGraduationAge($church);

        $dependants = MemberDependant::forChurch($church->id)
            ->eligibleForIndependence($graduationAge)
            ->with('member')
            ->get();

        $converted = 0;

        $settings = app(ChurchSettingsService::class);

        foreach ($dependants as $dependant) {
            $envelope = $this->findNextAvailableEnvelope($church, $dependant->member?->branch_id);
            $envelopeRequired = $settings->envelopeRequiredForAge($church, $dependant->age());

            if ($envelopeRequired && ! $envelope) {
                continue;
            }

            try {
                $this->convertChildToIndependentMember($dependant, $envelope);
                $converted++;
            } catch (\Throwable) {
                continue;
            }
        }

        return $converted;
    }

    public function findNextAvailableEnvelope(Church $church, ?int $branchId = null): ?string
    {
        for ($i = 1; $i <= 999; $i++) {
            $envelope = str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            if ($this->isEnvelopeAvailable($church, $envelope, null, $branchId)) {
                return $envelope;
            }
        }

        return null;
    }

    public function archive(Member $member, string $reason, ?int $archivedBy = null): Member
    {
        return DB::transaction(function () use ($member, $reason, $archivedBy) {
            $member->update([
                'status' => MemberStatus::Inactive,
                'archived_at' => now(),
                'archive_reason' => $reason,
                'archived_by' => $archivedBy ?? auth()->id(),
            ]);

            if ($member->user) {
                $member->user->update(['status' => UserStatus::Suspended]);
            }

            return $member->fresh(['archivedBy', 'user']);
        });
    }

    public function restore(Member $member): Member
    {
        if (! $member->isArchived()) {
            throw new \RuntimeException('This member is not archived.');
        }

        return DB::transaction(function () use ($member) {
            $member->update([
                'status' => MemberStatus::Active,
                'archived_at' => null,
                'archive_reason' => null,
                'archived_by' => null,
            ]);

            if ($member->user) {
                $member->user->update(['status' => UserStatus::Active]);
            }

            return $member->fresh(['user']);
        });
    }

    public function deleteMember(Member $member): void
    {
        DB::transaction(function () use ($member) {
            if ($member->user) {
                $member->user->delete();
            }

            $member->dependants()
                ->whereNull('linked_member_id')
                ->get()
                ->each(function (MemberDependant $dependant) {
                    $dependant->departments()->detach();
                    $dependant->delete();
                });

            $this->releaseEnvelopeNumbers($member);

            $member->delete();
        });
    }

    /**
     * Free envelope numbers so they can be reassigned after delete.
     * Clears the member's own numbers and any spouse copies that still point at them.
     */
    private function releaseEnvelopeNumbers(Member $member): void
    {
        $churchId = (int) $member->church_id;
        $envelope = $member->envelope_number;
        $spouseEnvelope = $member->spouse_envelope_number;
        $linkedSpouseId = $member->spouse_member_id ? (int) $member->spouse_member_id : null;
        $branchesEnabled = (bool) $member->church?->branches_enabled;
        $branchId = $member->branch_id;

        $member->forceFill([
            'envelope_number' => null,
            'spouse_envelope_number' => null,
            'spouse_member_id' => null,
        ])->save();

        // Unlink the surviving spouse so they no longer reserve this envelope.
        if ($linkedSpouseId) {
            Member::query()
                ->where('church_id', $churchId)
                ->whereKey($linkedSpouseId)
                ->update([
                    'spouse_member_id' => null,
                    'spouse_envelope_number' => null,
                ]);
        }

        Member::query()
            ->where('church_id', $churchId)
            ->where('spouse_member_id', $member->id)
            ->update([
                'spouse_member_id' => null,
                'spouse_envelope_number' => null,
            ]);

        foreach (array_filter([$envelope, $spouseEnvelope]) as $number) {
            $query = Member::query()
                ->where('church_id', $churchId)
                ->where('spouse_envelope_number', $number);

            if ($branchesEnabled) {
                $query->where('branch_id', $branchId);
            }

            $query->update(['spouse_envelope_number' => null]);
        }
    }

    public function resetMemberPassword(Member $member): string
    {
        $user = $member->user;

        if (! $user) {
            throw new \RuntimeException('No login account found for this member.');
        }

        $plainPassword = $this->passwordFromFullName($member->full_name);
        $user->update([
            'password' => $plainPassword,
            'status' => UserStatus::Active,
        ]);

        $church = $member->church;

        if ($church) {
            $user->loadMissing('member');
            $sms = $this->churchSmsService->sendPasswordReset($church, $user, $plainPassword);

            if (! ($sms['ok'] ?? false)) {
                Log::warning('Member password reset SMS not sent', [
                    'member_id' => $member->id,
                    'reason' => $sms['reason'] ?? 'unknown',
                ]);
            }
        }

        return $plainPassword;
    }

    public function convertToPermanent(Member $member, MemberType $memberType): Member
    {
        if ($member->membership_type !== MembershipType::Temporary) {
            throw new \RuntimeException('Only temporary members can be converted to permanent.');
        }

        $gender = match ($memberType) {
            MemberType::Father => 'male',
            MemberType::Mother => 'female',
            default => $member->gender,
        };

        $member->update([
            'membership_type' => MembershipType::Permanent,
            'member_type' => $memberType,
            'gender' => $gender,
            'temporary_duration_value' => null,
            'temporary_duration_unit' => null,
            'membership_expires_at' => null,
        ]);

        return $member->fresh();
    }

    public function extendTemporaryMembership(Member $member, int $value, TemporaryDurationUnit $unit): Member
    {
        if ($member->membership_type !== MembershipType::Temporary) {
            throw new \RuntimeException('Only temporary members can have their stay extended.');
        }

        $baseDate = $member->membership_expires_at && $member->membership_expires_at->isFuture()
            ? $member->membership_expires_at->toDateString()
            : now()->toDateString();

        $member->update([
            'temporary_duration_value' => $value,
            'temporary_duration_unit' => $unit,
            'membership_expires_at' => $this->calculateMembershipExpiresAt($value, $unit->value, $baseDate),
        ]);

        return $member->fresh();
    }

    public function calculateMembershipExpiresAt(int $value, string $unit, ?string $fromDate = null): string
    {
        $from = Carbon::parse($fromDate ?? now());

        return match ($unit) {
            TemporaryDurationUnit::Year->value => $from->copy()->addYears($value)->toDateString(),
            default => $from->copy()->addMonths($value)->toDateString(),
        };
    }

    private function applyMembershipDuration(array $data): array
    {
        if (($data['membership_type'] ?? null) === MembershipType::Temporary->value) {
            $value = (int) ($data['temporary_duration_value'] ?? 0);
            $unit = $data['temporary_duration_unit'] ?? TemporaryDurationUnit::Month->value;

            $data['member_type'] = null;
            $data['membership_expires_at'] = $this->calculateMembershipExpiresAt(
                $value,
                $unit,
                $data['membership_date'] ?? null
            );
        } else {
            $data['temporary_duration_value'] = null;
            $data['temporary_duration_unit'] = null;
            $data['membership_expires_at'] = null;
        }

        return $data;
    }

    public function generateMemberId(?Church $church = null): string
    {
        $year = now()->format('Y');
        $prefix = strtoupper($church
            ? (string) app(ChurchSettingsService::class)->get($church, 'member_id_prefix', config('waumini.member_id_suffix', 'WL'))
            : config('waumini.member_id_suffix', 'WL'));

        $query = Member::withTrashed()
            ->where('member_number', 'like', "{$prefix}-{$year}-%");

        if ($church) {
            $query->where('church_id', $church->id)->lockForUpdate();
        }

        $maxSequence = $query
            ->pluck('member_number')
            ->map(function (string $memberNumber) use ($prefix, $year) {
                if (preg_match('/^'.preg_quote($prefix, '/').'-'.preg_quote($year, '/').'-(\d+)$/', $memberNumber, $matches)) {
                    return (int) $matches[1];
                }

                return 0;
            })
            ->max();

        $sequence = ($maxSequence ?? 0) + 1;

        do {
            $memberId = sprintf('%s-%s-%04d', $prefix, $year, $sequence);
            $sequence++;
        } while (
            Member::withTrashed()->where('member_number', $memberId)->exists()
            || User::where('email', $memberId)->exists()
        );

        return $memberId;
    }

    public function passwordFromFullName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $lastName = $parts !== [] ? end($parts) : 'MEMBER';

        return strtoupper($lastName);
    }

    public function normalizePhoneNumber(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return $phone;
        }

        $value = preg_replace('/\s+/', '', $phone);

        if ($value === null || $value === '') {
            return $phone;
        }

        if (str_starts_with($value, '+255')) {
            return $value;
        }

        if (str_starts_with($value, '255') && strlen($value) > 9) {
            return '+'.$value;
        }

        if (str_starts_with($value, '0')) {
            $value = substr($value, 1);
        }

        return '+255'.$value;
    }

    public function normalizePersonName(?string $name): string
    {
        $normalized = preg_replace('/\s+/u', ' ', trim((string) $name));

        return mb_strtolower($normalized ?? '');
    }

    /**
     * Match an existing full member by normalized name + date of birth (+ gender when both set).
     *
     * @param  array{full_name?: string, date_of_birth?: mixed, gender?: mixed}  $person
     */
    public function findMatchingMember(Church $church, array $person, ?int $exceptMemberId = null): ?Member
    {
        $name = $this->normalizePersonName($person['full_name'] ?? null);
        $dob = $person['date_of_birth'] ?? null;

        if ($name === '' || empty($dob)) {
            return null;
        }

        try {
            $dobDate = Carbon::parse($dob)->toDateString();
        } catch (\Throwable) {
            return null;
        }

        $query = Member::forChurch($church->id)
            ->whereNotNull('date_of_birth')
            ->whereDate('date_of_birth', $dobDate);

        if ($exceptMemberId) {
            $query->whereKeyNot($exceptMemberId);
        }

        $gender = $person['gender'] ?? null;

        return $query->get()->first(function (Member $member) use ($name, $gender) {
            if ($this->normalizePersonName($member->full_name) !== $name) {
                return false;
            }

            if ($gender && $member->gender && (string) $member->gender !== (string) $gender) {
                return false;
            }

            return true;
        });
    }

    /**
     * Match an unconverted dependant (e.g. child under a parent) by name + DOB.
     *
     * @param  array{full_name?: string, date_of_birth?: mixed, gender?: mixed}  $person
     */
    public function findMatchingUnconvertedDependant(Church $church, array $person): ?MemberDependant
    {
        $name = $this->normalizePersonName($person['full_name'] ?? null);
        $dob = $person['date_of_birth'] ?? null;

        if ($name === '' || empty($dob)) {
            return null;
        }

        try {
            $dobDate = Carbon::parse($dob)->toDateString();
        } catch (\Throwable) {
            return null;
        }

        $gender = $person['gender'] ?? null;

        return MemberDependant::forChurch($church->id)
            ->whereNull('linked_member_id')
            ->where(function ($query) {
                $query->whereNull('member_id')
                    ->orWhereHas('member');
            })
            ->whereNotNull('date_of_birth')
            ->whereDate('date_of_birth', $dobDate)
            ->with('member')
            ->get()
            ->first(function (MemberDependant $dependant) use ($name, $gender) {
                if ($this->normalizePersonName($dependant->full_name) !== $name) {
                    return false;
                }

                if ($gender && $dependant->gender && (string) $dependant->gender !== (string) $gender) {
                    return false;
                }

                return true;
            });
    }

    public function assertNotAlreadyRegisteredMember(Church $church, array $person): void
    {
        $existing = $this->findMatchingMember($church, $person);

        if (! $existing) {
            return;
        }

        throw ValidationException::withMessages([
            'full_name' => 'This person is already registered as a member ('.$existing->member_number.'). One person cannot be registered twice.',
        ]);
    }

    private function applyExistingDependantFamilyLink(array $data, ?MemberDependant $matchingDependant): array
    {
        if (! $matchingDependant?->member_id) {
            return $data;
        }

        $memberType = $data['member_type'] ?? null;
        $isIndependent = $memberType === MemberType::Independent
            || $memberType === MemberType::Independent->value;

        if (! $isIndependent || ! empty($data['family_member_id'])) {
            return $data;
        }

        $data['family_parent_type'] = 'member';
        $data['family_member_id'] = $matchingDependant->member_id;

        if (empty(trim((string) ($data['guardian_relationship'] ?? '')))) {
            $data['guardian_relationship'] = 'Child';
        }

        return $data;
    }

    private function linkDependantToMember(MemberDependant $dependant, Member $member): void
    {
        $dependant->departments()->detach();
        $dependant->update(['linked_member_id' => $member->id]);
    }

    /**
     * @param  array{full_name?: string, gender?: mixed, date_of_birth?: mixed, relationship?: mixed, relationship_note?: mixed, linked_member_id?: mixed}  $dependant
     */
    private function createDependantForMember(Church $church, Member $member, array $dependant): ?MemberDependant
    {
        if (empty($dependant['full_name'])) {
            return null;
        }

        $existingDependant = $this->findMatchingUnconvertedDependant($church, $dependant);

        if ($existingDependant) {
            if ((int) $existingDependant->member_id === (int) $member->id) {
                return $existingDependant;
            }

            throw ValidationException::withMessages([
                'dependants' => $dependant['full_name'].' is already registered as a child under '.$existingDependant->guardianDisplayName().'.',
            ]);
        }

        $linkedMemberId = $dependant['linked_member_id'] ?? null;
        $matchingMember = $this->findMatchingMember($church, $dependant);

        if ($matchingMember) {
            $linkedMemberId = $matchingMember->id;
        }

        $settings = app(ChurchSettingsService::class);
        $dependantAge = $this->ageFromDateString($dependant['date_of_birth'] ?? null);
        $kipaimaraAllowed = $settings->canShowKipaimara($church, $dependantAge, false);
        $educationEnabled = (bool) $settings->get($church, 'children_education_details_enabled', false);

        $createdDependant = MemberDependant::create([
            'church_id' => $church->id,
            'member_id' => $member->id,
            'full_name' => $dependant['full_name'],
            'gender' => $dependant['gender'],
            'date_of_birth' => $dependant['date_of_birth'] ?? null,
            ...$this->normalizeDependantBaptismFields($dependant),
            ...($kipaimaraAllowed
                ? $this->normalizeDependantKipaimaraFields($dependant, true)
                : [
                    'is_kipaimara' => false,
                    'kipaimara_date' => null,
                    'kipaimara_place' => null,
                    'kipaimara_by' => null,
                ]),
            ...($educationEnabled
                ? $this->normalizeDependantEducationFields($dependant, true)
                : [
                    'is_student' => false,
                    'education_level' => null,
                    'school_name' => null,
                    'school_region' => null,
                    'school_district' => null,
                    'school_ward' => null,
                    'school_street' => null,
                ]),
            'relationship' => $dependant['relationship'],
            'relationship_note' => $dependant['relationship_note'] ?? null,
            'linked_member_id' => $linkedMemberId,
        ]);

        if (! $linkedMemberId) {
            $this->departmentAssignmentService->assignDependantIfApplicable($church, $createdDependant);
        }

        return $createdDependant;
    }

    private function createMemberUserAccount(Church $church, Member $member): void
    {
        if ($member->user()->exists()) {
            return;
        }

        $plainPassword = $this->passwordFromFullName($member->full_name);

        $user = User::create([
            'name' => $member->full_name,
            'email' => $member->member_number,
            'phone' => $member->phone_number,
            'password' => $plainPassword,
            'user_type' => UserType::Member,
            'status' => UserStatus::Active,
            'church_id' => $church->id,
            'member_id' => $member->id,
        ]);

        if (! $user->hasRole('member')) {
            $user->assignRole('member');
        }

        $this->registeredAccounts[] = [
            'name' => $member->full_name,
            'member_id' => $member->member_number,
            'password' => $plainPassword,
        ];

        $this->churchSmsService->sendMemberCredentials($church, $member, $plainPassword);
    }

    private function provisionSpouseMember(
        Church $church,
        Member $member,
        ?string $spouseInputMethod,
        ?int $selectedSpouseMemberId,
    ): ?Member {
        if ($member->marital_status !== MaritalStatus::Married) {
            return null;
        }

        if ($spouseInputMethod === 'select' && $selectedSpouseMemberId) {
            $this->linkSpouseMembers($member, $selectedSpouseMemberId);

            return Member::forChurch($church->id)->find($selectedSpouseMemberId);
        }

        if ($spouseInputMethod !== 'manual' || ! $this->canCreateSpouseMember($member)) {
            return null;
        }

        $existingSpouse = $this->findExistingSpouse($church, $member);

        if ($existingSpouse) {
            $member->update(['spouse_member_id' => $existingSpouse->id]);
            $this->linkSpouseMembers($member, $existingSpouse->id);

            return $existingSpouse;
        }

        $spouseMember = $this->createSpouseMember($church, $member);
        $member->update(['spouse_member_id' => $spouseMember->id]);
        $this->spouseMemberCreated = true;

        return $spouseMember;
    }

    private function canCreateSpouseMember(Member $member): bool
    {
        return ! empty($member->spouse_full_name)
            && ! empty($member->spouse_envelope_number);
    }

    private function findExistingSpouse(Church $church, Member $member): ?Member
    {
        $query = Member::forChurch($church->id)
            ->whereKeyNot($member->id)
            ->where('full_name', $member->spouse_full_name);

        if (! empty($member->spouse_phone_number)) {
            $query->where('phone_number', $member->spouse_phone_number);
        }

        return $query->first();
    }

    private function createSpouseMember(Church $church, Member $member): Member
    {
        $spouseMemberType = match ($member->member_type) {
            MemberType::Father => MemberType::Mother,
            MemberType::Mother => MemberType::Father,
            default => MemberType::Independent,
        };

        $spouseGender = $member->spouse_gender;
        if (! $spouseGender) {
            $spouseGender = match ($member->member_type) {
                MemberType::Father => 'female',
                MemberType::Mother => 'male',
                default => $member->gender === 'male' ? 'female' : 'male',
            };
        }

        $spouseEnvelope = $member->spouse_envelope_number;

        if ($spouseEnvelope && ! $this->isEnvelopeAvailable($church, $spouseEnvelope, $member->id, $member->branch_id)) {
            throw ValidationException::withMessages([
                'spouse_envelope_number' => 'Spouse envelope number is already in use in this branch.',
            ]);
        }

        try {
            return Member::create([
                'church_id' => $church->id,
                'branch_id' => $member->branch_id,
                'member_number' => $this->generateMemberId($church),
                'envelope_number' => $spouseEnvelope,
                'member_type' => $spouseMemberType,
                'membership_type' => $member->membership_type,
                'temporary_duration_value' => $member->temporary_duration_value,
                'temporary_duration_unit' => $member->temporary_duration_unit,
                'membership_expires_at' => $member->membership_expires_at,
                'full_name' => $member->spouse_full_name,
                'email' => $member->spouse_email,
                'phone_number' => $this->normalizePhoneNumber($member->spouse_phone_number),
                'gender' => $spouseGender,
                'date_of_birth' => $member->spouse_date_of_birth,
                'education_level' => $member->spouse_education_level,
                'profession' => $member->spouse_profession,
                'nida_number' => $member->spouse_nida_number,
                'tribe' => $member->spouse_tribe,
                'other_tribe' => $member->spouse_other_tribe,
                'region' => $member->region,
                'district' => $member->district,
                'ward' => $member->ward,
                'street' => $member->street,
                'po_box' => $member->po_box,
                'residence_region' => $member->residence_region,
                'residence_district' => $member->residence_district,
                'residence_ward' => $member->residence_ward,
                'residence_street' => $member->residence_street,
                'residence_road' => $member->residence_road,
                'residence_house_number' => $member->residence_house_number,
                'marital_status' => MaritalStatus::Married,
                'spouse_church_member' => 'yes',
                'spouse_member_id' => $member->id,
                'spouse_full_name' => $member->full_name,
                'spouse_gender' => $member->gender,
                'spouse_date_of_birth' => $member->date_of_birth,
                'spouse_phone_number' => $member->phone_number,
                'spouse_email' => $member->email,
                'spouse_envelope_number' => $member->envelope_number,
                'membership_date' => $member->membership_date,
                'status' => $member->status,
            ]);
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'spouse_envelope_number' => 'Spouse envelope number is already in use in this branch.',
            ]);
        }
    }

    private function linkSpouseMembers(Member $member, int $spouseId): void
    {
        $spouse = Member::forChurch($member->church_id)->find($spouseId);

        if (! $spouse || $spouse->id === $member->id) {
            return;
        }

        $spouse->update([
            'marital_status' => MaritalStatus::Married,
            'spouse_church_member' => 'yes',
            'spouse_member_id' => $member->id,
            'spouse_full_name' => $member->full_name,
            'spouse_gender' => $member->gender,
            'spouse_date_of_birth' => $member->date_of_birth,
            'spouse_phone_number' => $member->phone_number,
            'spouse_email' => $member->email,
            'spouse_envelope_number' => $member->envelope_number,
        ]);
    }

    private function clearSpouseFields(array $data): array
    {
        foreach (array_keys($data) as $key) {
            if (str_starts_with($key, 'spouse_')) {
                $data[$key] = null;
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeFamilyLink(array $data): array
    {
        $memberType = $data['member_type'] ?? null;
        $isIndependent = $memberType === MemberType::Independent
            || $memberType === MemberType::Independent->value;

        $linkType = $data['family_parent_type'] ?? null;

        if (! $isIndependent) {
            $data['family_member_id'] = null;
            $data['secondary_family_member_id'] = null;
            $data['guardian_full_name'] = null;
            $data['guardian_phone'] = null;
            $data['guardian_relationship'] = null;

            return $data;
        }

        // Independents are treated as unmarried in registration.
        $data['marital_status'] = MaritalStatus::Single->value;
        $data = $this->clearSpouseFields($data);
        $data['wedding_type'] = null;
        $data['wedding_date'] = null;

        $relationship = trim((string) ($data['guardian_relationship'] ?? '')) ?: null;

        if ($linkType === 'member') {
            $familyMemberId = ! empty($data['family_member_id'])
                ? (int) $data['family_member_id']
                : null;

            $data['family_member_id'] = $familyMemberId;
            $data['secondary_family_member_id'] = $familyMemberId
                ? $this->resolveSecondaryFamilyMemberId($familyMemberId)
                : null;
            $data['guardian_full_name'] = null;
            $data['guardian_phone'] = null;
            $data['guardian_relationship'] = $relationship;

            return $data;
        }

        if ($linkType === 'guardian') {
            $data['family_member_id'] = null;
            $data['secondary_family_member_id'] = null;
            $data['guardian_full_name'] = trim((string) ($data['guardian_full_name'] ?? '')) ?: null;
            $data['guardian_relationship'] = $relationship;
            $phone = $data['guardian_phone'] ?? null;
            $data['guardian_phone'] = $phone
                ? ($this->normalizePhoneNumber($phone) ?: $phone)
                : null;

            return $data;
        }

        // Keep existing DB values on update if the form did not send family_parent_type.
        if ($linkType === null && ! array_key_exists('family_member_id', $data) && ! array_key_exists('guardian_full_name', $data)) {
            return $data;
        }

        $data['family_member_id'] = null;
        $data['secondary_family_member_id'] = null;
        $data['guardian_full_name'] = null;
        $data['guardian_phone'] = null;
        $data['guardian_relationship'] = null;

        return $data;
    }

    private function resolveSecondaryFamilyMemberId(int $familyMemberId): ?int
    {
        $familyMember = Member::query()
            ->with(['spouseMember', 'spouseOf'])
            ->find($familyMemberId);

        if (! $familyMember) {
            return null;
        }

        $spouse = $familyMember->resolvedSpouse();

        if (! $spouse || (int) $spouse->id === $familyMemberId) {
            return null;
        }

        return (int) $spouse->id;
    }

    private function resolveBranchId(Church $church, ?int $branchId): ?int
    {
        if (! $church->branchesEnabled()) {
            return null;
        }

        if ($branchId) {
            abort_unless(
                ChurchBranch::forChurch($church->id)->whereKey($branchId)->exists(),
                422,
                'Selected branch is invalid.'
            );

            return $branchId;
        }

        return ChurchBranch::forChurch($church->id)
            ->where('is_headquarters', true)
            ->value('id');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeBaptismFields(array $data): array
    {
        $data['is_baptized'] = filter_var($data['is_baptized'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $data['is_baptized']) {
            $data['baptism_date'] = null;
            $data['baptism_place'] = null;
            $data['baptized_by'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function ageFromDateString(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->age;
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeKipaimaraFields(array $data, bool $allow, bool $forceClear = false): array
    {
        if (! $allow) {
            if ($forceClear) {
                $data['is_kipaimara'] = false;
                $data['kipaimara_date'] = null;
                $data['kipaimara_place'] = null;
                $data['kipaimara_by'] = null;

                return $data;
            }

            unset(
                $data['is_kipaimara'],
                $data['kipaimara_date'],
                $data['kipaimara_place'],
                $data['kipaimara_by'],
            );

            return $data;
        }

        $data['is_kipaimara'] = filter_var($data['is_kipaimara'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $data['is_kipaimara']) {
            $data['kipaimara_date'] = null;
            $data['kipaimara_place'] = null;
            $data['kipaimara_by'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $dependant
     * @return array<string, mixed>
     */
    private function normalizeDependantBaptismFields(array $dependant): array
    {
        $isBaptized = filter_var($dependant['is_baptized'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return [
            'is_baptized' => $isBaptized,
            'baptism_date' => $isBaptized ? ($dependant['baptism_date'] ?? null) : null,
            'baptism_place' => $isBaptized ? ($dependant['baptism_place'] ?? null) : null,
            'baptized_by' => $isBaptized ? ($dependant['baptized_by'] ?? null) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $dependant
     * @return array<string, mixed>
     */
    private function normalizeDependantKipaimaraFields(array $dependant, bool $allow): array
    {
        if (! $allow) {
            return [];
        }

        $isKipaimara = filter_var($dependant['is_kipaimara'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return [
            'is_kipaimara' => $isKipaimara,
            'kipaimara_date' => $isKipaimara ? ($dependant['kipaimara_date'] ?? null) : null,
            'kipaimara_place' => $isKipaimara ? ($dependant['kipaimara_place'] ?? null) : null,
            'kipaimara_by' => $isKipaimara ? ($dependant['kipaimara_by'] ?? null) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $dependant
     * @return array<string, mixed>
     */
    private function normalizeDependantEducationFields(array $dependant, bool $allow): array
    {
        if (! $allow) {
            return [];
        }

        $isStudent = filter_var($dependant['is_student'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $isStudent) {
            return [
                'is_student' => false,
                'education_level' => null,
                'school_name' => null,
                'school_region' => null,
                'school_district' => null,
                'school_ward' => null,
                'school_street' => null,
            ];
        }

        return [
            'is_student' => true,
            'education_level' => $dependant['education_level'] ?? null,
            'school_name' => trim((string) ($dependant['school_name'] ?? '')) ?: null,
            'school_region' => trim((string) ($dependant['school_region'] ?? '')) ?: null,
            'school_district' => trim((string) ($dependant['school_district'] ?? '')) ?: null,
            'school_ward' => trim((string) ($dependant['school_ward'] ?? '')) ?: null,
            'school_street' => trim((string) ($dependant['school_street'] ?? '')) ?: null,
        ];
    }
}
