<?php

namespace App\Http\Requests\Church;

use App\Enums\FinancePaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordPledgePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('recordPayment', $this->route('pledge'));
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['required', Rule::enum(FinancePaymentMethod::class)],
            'reference_number' => [
                'nullable',
                'required_if:payment_method,bank_transfer,mobile_money,cheque',
                'string',
                'max:255',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
