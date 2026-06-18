<?php

namespace App\Http\Requests\Church;

use App\Enums\FinancePaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTitheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Tithe::class);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'member_id' => [
                'required',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)->where('status', 'active')),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tithe_date' => ['required', 'date', 'before_or_equal:today'],
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
