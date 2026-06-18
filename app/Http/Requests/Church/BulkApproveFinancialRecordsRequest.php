<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkApproveFinancialRecordsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('finance.approve');
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'min:1'],
            'records.*.type' => ['required', Rule::in(['tithe', 'offering', 'pledge_payment', 'budget', 'expense'])],
            'records.*.id' => ['required', 'integer', 'min:1'],
            'approval_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
