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
use App\Services\Sms\ChurchSmsService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MemberService
{
    private bool $spouseMemberCreated = false;

    /** @var array<int, array{name: string, member_id: string, password: string}> */
    private array $registeredAccounts = [];

    public function __construct(
        private readonly ChurchSmsService $churchSmsService,
        private readonly CelebrationService $celebrationService,
    ) {}

    public function create(Church $church, array $data, ?UploadedFile $profilePicture = null, array $dependants = []): Member
    {
        $this->spouseMemberCreated = false;
        $this->registeredAccounts = [];

        return DB::transaction(function () use ($church, $data, $profilePicture, $dependants) {
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

            unset($data['spouse_input_method'], $data['dependants']);

            $member = Member::create($data);
            $this->createMemberUserAccount($church, $member);

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
                if (empty($dependant['full_name'])) {
                    continue;
                }

                MemberDependant::create([
                    'church_id' => $church->id,
                    'member_id' => $member->id,
                    'full_name' => $dependant['full_name'],
                    'gender' => $dependant['gender'],
                    'date_of_birth' => $dependant['date_of_birth'] ?? null,
                    ...$this->normalizeDependantBaptismFields($dependant),
                    'relationship' => $dependant['relationship'],
                    'relationship_note' => $dependant['relationship_note'] ?? null,
                    'linked_member_id' => $dependant['linked_member_id'] ?? null,
                ]);
            }

            $this->celebrationService->syncMember($member);

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

            unset($data['member_number'], $data['church_id'], $data['spouse_input_method'], $data['dependants']);

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

            $member->update($data);

            $spouseMember = null;

            if (! $hadLinkedSpouse) {
                $spouseMember = $this->provisionSpouseMember(
                    $church,
                    $member->fresh(),
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
        $guardianPhone = $data['guardian_phone'] ?? null;
        if ($guardianPhone) {
            $guardianPhone = $this->normalizePhoneNumber($guardianPhone) ?: $guardianPhone;
        }

        return MemberDependant::create([
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
        ]);
    }

    public function spouseMemberWasCreated(): bool
    {
        return $this->spouseMemberCreated;
    }

    /**
     * @return array<int, array{name: string, member_id: string, password: string}>
     */
    public function getRegisteredAccounts(): array
    {
        return $this->registeredAccounts;
    }

    public function isEnvelopeAvailable(Church $church, string $envelope, ?int $exceptMemberId = null): bool
    {
        if (strlen($envelope) !== 3 || ! ctype_digit($envelope)) {
            return false;
        }

        $query = Member::forChurch($church->id)
            ->where(function ($q) use ($envelope) {
                $q->where('envelope_number', $envelope)
                    ->orWhere('spouse_envelope_number', $envelope);
            });

        if ($exceptMemberId) {
            $query->whereKeyNot($exceptMemberId);
        }

        return ! $query->exists();
    }

    public function convertChildToIndependentMember(
        MemberDependant $dependant,
        string $envelopeNumber,
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

            if ($dependant->relationship !== DependantRelationship::Child) {
                throw new \RuntimeException('Only children can be converted to independent members.');
            }

            if ($dependant->isConverted()) {
                throw new \RuntimeException('This child has already been converted to an independent member.');
            }

            if (! $dependant->isEligibleForIndependence()) {
                throw new \RuntimeException(
                    'Child must be at least '.config('membership.child_independence_age', 21).' years old with a date of birth on file.'
                );
            }

            if (! $this->isEnvelopeAvailable($church, $envelopeNumber)) {
                throw new \RuntimeException('Envelope number is already in use.');
            }

            $phone = $phoneNumber
                ? $this->normalizePhoneNumber($phoneNumber)
                : $this->normalizePhoneNumber($parent?->phone_number ?? $dependant->guardian_phone);

            $memberData = [
                'church_id' => $church->id,
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

            $member = Member::create($memberData);

            $dependant->update(['linked_member_id' => $member->id]);

            $this->createMemberUserAccount($church, $member);

            return $member->fresh(['user']);
        });
    }

    public function processAgedOutChildren(Church $church): int
    {
        $dependants = MemberDependant::forChurch($church->id)
            ->eligibleForIndependence()
            ->with('member')
            ->get();

        $converted = 0;

        foreach ($dependants as $dependant) {
            $envelope = $this->findNextAvailableEnvelope($church);

            if (! $envelope) {
                break;
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

    public function findNextAvailableEnvelope(Church $church): ?string
    {
        for ($i = 1; $i <= 999; $i++) {
            $envelope = str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            if ($this->isEnvelopeAvailable($church, $envelope)) {
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

            $member->delete();
        });
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

        return Member::create([
            'church_id' => $church->id,
            'branch_id' => $member->branch_id,
            'member_number' => $this->generateMemberId($church),
            'envelope_number' => $member->spouse_envelope_number,
            'member_type' => $spouseMemberType,
            'membership_type' => $member->membership_type,
            'temporary_duration_value' => $member->temporary_duration_value,
            'temporary_duration_unit' => $member->temporary_duration_unit,
            'membership_expires_at' => $member->membership_expires_at,
            'full_name' => $member->spouse_full_name,
            'email' => $member->spouse_email,
            'phone_number' => $member->spouse_phone_number,
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
}
