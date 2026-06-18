<?php

namespace App\Http\Requests\Church;

class StoreOfferingRequest extends OfferingRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Offering::class);
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
