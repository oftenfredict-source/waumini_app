<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarkInvoicePaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('markPaid', $this->route('invoice'));
    }

    public function rules(): array
    {
        return [
            'method' => ['nullable', 'string', Rule::in(['cash', 'bank_transfer', 'mobile_money', 'cheque', 'other'])],
            'provider_reference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentInput(): array
    {
        return [
            'method' => $this->input('method', 'cash'),
            'provider_reference' => $this->input('provider_reference'),
            'notes' => $this->input('notes'),
        ];
    }
}
