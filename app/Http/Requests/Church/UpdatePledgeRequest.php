<?php

namespace App\Http\Requests\Church;

use App\Enums\PledgePaymentFrequency;
use App\Enums\PledgeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('pledge'));
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'member_id' => [
                'required',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)->where('status', 'active')),
            ],
            'pledge_amount' => ['required', 'numeric', 'min:0.01'],
            'pledge_date' => ['required', 'date'],
            'pledge_type' => ['required', Rule::enum(PledgeType::class)],
            'pledge_type_other' => ['nullable', 'required_if:pledge_type,other', 'string', 'max:100'],
            'payment_frequency' => ['required', Rule::enum(PledgePaymentFrequency::class)],
            'one_time_payment_date' => ['nullable', 'required_if:payment_frequency,one_time', 'date', 'after_or_equal:pledge_date'],
            'purpose' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'pledge_type_other.required_if' => 'Please specify the pledge type.',
            'one_time_payment_date.required_if' => 'Please set the one-time payment date.',
        ];
    }
}
