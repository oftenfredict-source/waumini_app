<?php

namespace App\Http\Requests\Church;

class UpdateOfferingRequest extends OfferingRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('offering'));
    }

    public function rules(): array
    {
        return $this->offeringRules();
    }

    public function messages(): array
    {
        return $this->offeringMessages();
    }
}
