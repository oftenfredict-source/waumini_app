<?php

namespace App\Http\Requests\Church;

use App\Enums\DependantRelationship;
use App\Enums\EducationLevel;
use App\Enums\MaritalStatus;
use App\Enums\MemberType;
use App\Enums\MembershipType;
use App\Enums\TemporaryDurationUnit;
use App\Enums\WeddingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('members.create');
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $branchesEnabled = (bool) $this->user()->church?->branches_enabled;
        $isMarried = $this->input('marital_status') === MaritalStatus::Married->value;
        $isPermanent = $this->input('membership_type') === MembershipType::Permanent->value;
        $isTemporary = $this->input('membership_type') === MembershipType::Temporary->value;
        $spouseIsMember = $this->input('spouse_church_member') === 'yes';
        $spouseUsesSelect = $isMarried && $spouseIsMember && $this->input('spouse_input_method') === 'select';
        $spouseUsesManual = $isMarried && (! $spouseIsMember || $this->input('spouse_input_method') === 'manual');

        return [
            'membership_type' => ['required', Rule::enum(MembershipType::class)],
            'branch_id' => $branchesEnabled ? [
                'nullable',
                'integer',
                Rule::exists('church_branches', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ] : ['nullable', 'prohibited'],
            'temporary_duration_value' => [
                Rule::requiredIf($isTemporary),
                'nullable',
                'integer',
                'min:1',
                'max:99',
            ],
            'temporary_duration_unit' => [
                Rule::requiredIf($isTemporary),
                'nullable',
                Rule::enum(TemporaryDurationUnit::class),
            ],
            'member_type' => [
                Rule::requiredIf($isPermanent),
                'nullable',
                Rule::enum(MemberType::class),
            ],
            'envelope_number' => [
                'required',
                'string',
                'digits:3',
                Rule::unique('members', 'envelope_number')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
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
            'spouse_church_member' => [Rule::requiredIf($isMarried), 'nullable', Rule::in(['yes', 'no'])],
            'spouse_input_method' => [
                Rule::requiredIf($isMarried && $spouseIsMember),
                'nullable',
                Rule::in(['select', 'manual']),
            ],
            'spouse_full_name' => [Rule::requiredIf($spouseUsesManual), 'nullable', 'string', 'max:255'],
            'spouse_gender' => [Rule::requiredIf($spouseUsesManual), 'nullable', Rule::in(['male', 'female'])],
            'spouse_date_of_birth' => [Rule::requiredIf($spouseUsesManual), 'nullable', 'date', 'before:today'],
            'spouse_education_level' => ['nullable', Rule::enum(EducationLevel::class)],
            'spouse_profession' => ['nullable', 'string', 'max:150'],
            'spouse_nida_number' => ['nullable', 'string', 'max:50'],
            'spouse_email' => ['nullable', 'email', 'max:255'],
            'spouse_phone_number' => ['nullable', 'string', 'max:30'],
            'spouse_tribe' => ['nullable', 'string', 'max:100'],
            'spouse_other_tribe' => ['required_if:spouse_tribe,Other', 'nullable', 'string', 'max:100'],
            'spouse_member_id' => [
                Rule::requiredIf($spouseUsesSelect),
                'nullable',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'spouse_envelope_number' => [
                Rule::requiredIf($isMarried && $spouseIsMember && $this->input('spouse_input_method') === 'manual'),
                'nullable',
                'string',
                'digits:3',
                'different:envelope_number',
                Rule::when(
                    $spouseUsesManual,
                    Rule::unique('members', 'envelope_number')->where(fn ($q) => $q->where('church_id', $churchId))
                ),
            ],

            'dependants' => ['nullable', 'array'],
            'dependants.*.full_name' => ['required_with:dependants', 'string', 'max:255'],
            'dependants.*.gender' => ['required_with:dependants', Rule::in(['male', 'female'])],
            'dependants.*.date_of_birth' => ['nullable', 'date', 'before:today'],
            'dependants.*.relationship' => ['required_with:dependants', Rule::enum(DependantRelationship::class)],
            'dependants.*.relationship_note' => ['nullable', 'string', 'max:150'],
            'dependants.*.is_baptized' => ['nullable', 'boolean'],
            'dependants.*.baptism_date' => ['nullable', 'date', 'before_or_equal:today'],
            'dependants.*.baptism_place' => ['nullable', 'string', 'max:255'],
            'dependants.*.baptized_by' => ['nullable', 'string', 'max:255'],
            'dependants.*.linked_member_id' => [
                'nullable',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
        ];
    }
}
