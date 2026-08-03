<?php

namespace App\Http\Requests\Church;

use App\Models\Member;
use App\Models\MemberRegistrationApplication;
use App\Services\Church\ChurchSettingsService;
use App\Services\Church\MemberRegistrationApplicationService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveMemberRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('review', $this->route('registration'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'envelope_number' => $this->filled('envelope_number') ? $this->string('envelope_number')->trim()->toString() : null,
            'spouse_envelope_number' => $this->filled('spouse_envelope_number') ? $this->string('spouse_envelope_number')->trim()->toString() : null,
        ]);
    }

    public function rules(): array
    {
        $church = $this->user()->church;
        $branchesEnabled = (bool) $church?->branches_enabled;
        $settings = app(ChurchSettingsService::class);
        $needsSpouseEnvelope = $this->registrationNeedsSpouseEnvelope();
        /** @var MemberRegistrationApplication $application */
        $application = $this->route('registration');
        $branchId = $application->branch_id
            ?? ($application->registration_data['branch_id'] ?? null);
        $data = $application->registration_data ?? [];
        $applicantAge = $this->ageFromValue($data['date_of_birth'] ?? null);
        $spouseAge = $this->ageFromValue($data['spouse_date_of_birth'] ?? null);
        $spouseEnvelopeRequired = $needsSpouseEnvelope && $settings->envelopeRequiredForAge($church, $spouseAge);

        return [
            'envelope_number' => Member::envelopeValidationRules(
                $church,
                $applicantAge,
                $branchId ? (int) $branchId : null,
                $branchesEnabled,
            ),
            'spouse_envelope_number' => [
                Rule::requiredIf($spouseEnvelopeRequired),
                'nullable',
                'string',
                'digits:3',
                'different:envelope_number',
                Rule::when(
                    $needsSpouseEnvelope && $this->filled('spouse_envelope_number'),
                    Member::uniqueEnvelopeRule($church->id, $branchId ? (int) $branchId : null, $branchesEnabled)
                ),
            ],
        ];
    }

    private function registrationNeedsSpouseEnvelope(): bool
    {
        $application = $this->route('registration');

        return MemberRegistrationApplicationService::needsSpouseEnvelope(
            $application?->registration_data ?? []
        );
    }

    private function ageFromValue(mixed $value): ?int
    {
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
