<?php

namespace App\Http\Requests\Church;

use App\Enums\EducationLevel;
use App\Enums\MaritalStatus;
use App\Enums\MemberType;
use App\Enums\MembershipType;
use App\Enums\WeddingType;
use App\Models\Member;
use App\Services\Church\ChurchSettingsService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->route('member');

        return $member instanceof Member && $this->user()->can('update', $member);
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        if ($this->exists('envelope_number')) {
            $merge['envelope_number'] = $this->filled('envelope_number')
                ? $this->string('envelope_number')->trim()->toString()
                : null;
        }

        // Only touch spouse envelope when the field was actually submitted,
        // so edit forms with a linked spouse do not wipe it to null.
        if ($this->exists('spouse_envelope_number')) {
            $merge['spouse_envelope_number'] = $this->filled('spouse_envelope_number')
                ? $this->string('spouse_envelope_number')->trim()->toString()
                : null;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Member $member */
        $member = $this->route('member');
        $church = $this->user()->church;
        $churchId = $church->id;
        $settings = app(ChurchSettingsService::class);
        $branchesEnabled = (bool) $church?->branches_enabled;
        $branchId = $branchesEnabled
            ? ($this->integer('branch_id') ?: $member->branch_id)
            : null;
        $memberAge = $this->ageFromInput('date_of_birth') ?? $member->date_of_birth?->age;
        $spouseAge = $this->ageFromInput('spouse_date_of_birth');
        $isMarried = $this->input('marital_status') === MaritalStatus::Married->value;
        $isPermanent = $this->input('membership_type') === MembershipType::Permanent->value;
        $spouseIsMember = $this->input('spouse_church_member') === 'yes';
        $spouseUsesSelect = $isMarried && $spouseIsMember && $this->input('spouse_input_method') === 'select';
        $spouseUsesManual = $isMarried && (! $spouseIsMember || $this->input('spouse_input_method') === 'manual');
        $hasLinkedSpouse = (bool) $member->spouse_member_id;
        $canSetSpouse = $isMarried && ! $hasLinkedSpouse;
        $spouseEnvelopeRequired = $canSetSpouse && $spouseUsesManual && $settings->envelopeRequiredForAge($church, $spouseAge);
        $isIndependent = $isPermanent && $this->input('member_type') === MemberType::Independent->value;
        $familyUsesMember = $isIndependent && $this->input('family_parent_type') === 'member';
        $familyUsesGuardian = $isIndependent && $this->input('family_parent_type') === 'guardian';

        return [
            'envelope_number' => Member::envelopeValidationRules(
                $church,
                $memberAge,
                $branchId,
                $branchesEnabled,
                $member->id,
            ),
            'membership_type' => ['required', Rule::enum(MembershipType::class)],
            'member_type' => [
                Rule::requiredIf($isPermanent),
                'nullable',
                Rule::enum(MemberType::class),
            ],
            'branch_id' => $branchesEnabled ? [
                'nullable',
                'integer',
                Rule::exists('church_branches', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ] : ['nullable', 'prohibited'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'education_level' => ['nullable', Rule::enum(EducationLevel::class)],
            'profession' => ['nullable', 'string', 'max:150'],
            'nida_number' => ['nullable', 'string', 'max:50'],
            'is_baptized' => ['nullable', 'boolean'],
            'baptism_date' => ['nullable', 'date', 'before_or_equal:today'],
            'baptism_place' => ['nullable', 'string', 'max:255'],
            'baptized_by' => ['nullable', 'string', 'max:255'],
            'is_kipaimara' => ['nullable', 'boolean'],
            'kipaimara_date' => ['nullable', 'date', 'before_or_equal:today'],
            'kipaimara_place' => ['nullable', 'string', 'max:255'],
            'kipaimara_by' => ['nullable', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'region' => ['nullable', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'ward' => ['nullable', 'string', 'max:100'],
            'street' => ['nullable', 'string', 'max:150'],
            'po_box' => ['nullable', 'string', 'max:100'],
            'tribe' => ['nullable', 'string', 'max:100'],
            'other_tribe' => ['required_if:tribe,Other', 'nullable', 'string', 'max:100'],
            'residence_region' => ['nullable', 'string', 'max:100'],
            'residence_district' => ['nullable', 'string', 'max:100'],
            'residence_ward' => ['nullable', 'string', 'max:100'],
            'residence_street' => ['nullable', 'string', 'max:150'],
            'residence_road' => ['nullable', 'string', 'max:150'],
            'residence_house_number' => ['nullable', 'string', 'max:50'],
            'marital_status' => ['required', Rule::enum(MaritalStatus::class)],
            'wedding_type' => [Rule::requiredIf($isMarried), 'nullable', Rule::enum(WeddingType::class)],
            'wedding_date' => ['nullable', 'date', 'before_or_equal:today'],
            'spouse_church_member' => [Rule::requiredIf($canSetSpouse), 'nullable', Rule::in(['yes', 'no'])],
            'spouse_input_method' => [
                Rule::requiredIf($canSetSpouse && $spouseIsMember),
                'nullable',
                Rule::in(['select', 'manual']),
            ],
            'spouse_full_name' => [Rule::requiredIf($canSetSpouse && $spouseUsesManual), 'nullable', 'string', 'max:255'],
            'spouse_gender' => [Rule::requiredIf($canSetSpouse && $spouseUsesManual), 'nullable', Rule::in(['male', 'female'])],
            'spouse_date_of_birth' => [Rule::requiredIf($canSetSpouse && $spouseUsesManual), 'nullable', 'date', 'before:today'],
            'spouse_education_level' => ['nullable', Rule::enum(EducationLevel::class)],
            'spouse_profession' => ['nullable', 'string', 'max:150'],
            'spouse_nida_number' => ['nullable', 'string', 'max:50'],
            'spouse_email' => ['nullable', 'email', 'max:255'],
            'spouse_phone_number' => ['nullable', 'string', 'max:30'],
            'spouse_tribe' => ['nullable', 'string', 'max:100'],
            'spouse_other_tribe' => ['required_if:spouse_tribe,Other', 'nullable', 'string', 'max:100'],
            'spouse_member_id' => [
                Rule::requiredIf($canSetSpouse && $spouseUsesSelect),
                'nullable',
                'integer',
                Rule::notIn([$member->id]),
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'spouse_envelope_number' => [
                Rule::requiredIf($spouseEnvelopeRequired),
                'nullable',
                'string',
                'digits:3',
                'different:envelope_number',
                Rule::when(
                    $canSetSpouse && $spouseUsesManual && $this->filled('spouse_envelope_number'),
                    Member::uniqueEnvelopeRule($churchId, $branchId, $branchesEnabled, $member->id)
                ),
            ],
            'family_parent_type' => [
                Rule::requiredIf($isIndependent),
                'nullable',
                Rule::in(['member', 'guardian']),
            ],
            'family_member_id' => [
                Rule::requiredIf($familyUsesMember),
                'nullable',
                'integer',
                Rule::notIn([$member->id]),
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'guardian_full_name' => [
                Rule::requiredIf($familyUsesGuardian),
                'nullable',
                'string',
                'max:255',
            ],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_relationship' => [
                Rule::requiredIf($isIndependent),
                'nullable',
                'string',
                'max:50',
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function ageFromInput(string $key): ?int
    {
        $value = $this->input($key);

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->age;
        } catch (\Throwable) {
            return null;
        }
    }
}
