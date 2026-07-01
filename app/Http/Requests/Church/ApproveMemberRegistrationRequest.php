<?php

namespace App\Http\Requests\Church;

use App\Services\Church\MemberRegistrationApplicationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApproveMemberRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('review', $this->route('registration'));
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $needsSpouseEnvelope = $this->registrationNeedsSpouseEnvelope();

        return [
            'envelope_number' => [
                'required',
                'string',
                'digits:3',
                Rule::unique('members', 'envelope_number')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'spouse_envelope_number' => [
                Rule::requiredIf($needsSpouseEnvelope),
                'nullable',
                'string',
                'digits:3',
                'different:envelope_number',
                Rule::when(
                    $needsSpouseEnvelope,
                    Rule::unique('members', 'envelope_number')->where(fn ($q) => $q->where('church_id', $churchId))
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
}
