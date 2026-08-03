<?php

namespace App\Http\Requests\Church;

use App\Models\Member;
use App\Models\MemberDependant;
use App\Services\Church\ChurchSettingsService;
use Illuminate\Foundation\Http\FormRequest;

class ConvertChildToMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('convert', $this->route('dependant'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'envelope_number' => $this->filled('envelope_number') ? $this->string('envelope_number')->trim()->toString() : null,
        ]);
    }

    public function rules(): array
    {
        $church = $this->user()->church;
        $branchesEnabled = (bool) $church?->branches_enabled;
        /** @var MemberDependant $dependant */
        $dependant = $this->route('dependant');
        $dependant->loadMissing('member:id,branch_id');
        $branchId = $dependant->member?->branch_id;

        return [
            'envelope_number' => Member::envelopeValidationRules(
                $church,
                $dependant->age(),
                $branchId,
                $branchesEnabled,
            ),
            'phone_number' => ['nullable', 'string', 'max:30'],
        ];
    }
}
