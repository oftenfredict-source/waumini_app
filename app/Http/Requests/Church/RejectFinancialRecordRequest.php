<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RejectFinancialRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.approve');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['tithe', 'offering', 'pledge_payment', 'budget', 'expense'])],
            'id' => ['required', 'integer', 'min:1'],
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
