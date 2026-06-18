<?php

namespace App\Http\Requests\Church;

use App\Enums\FinancePaymentMethod;
use App\Enums\OfferingContributionType;
use App\Enums\OfferingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class OfferingRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function offeringRules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'contribution_type' => ['required', Rule::enum(OfferingContributionType::class)],
            'member_id' => [
                'nullable',
                'required_if:contribution_type,member',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)->where('status', 'active')),
            ],
            'church_service_id' => [
                'nullable',
                'required_if:contribution_type,general',
                Rule::exists('church_services', 'id')->where(fn ($q) => $q
                    ->where('church_id', $churchId)
                    ->where('status', '!=', 'cancelled')),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'offering_date' => ['required', 'date', 'before_or_equal:today'],
            'offering_type' => ['required', Rule::enum(OfferingType::class)],
            'offering_type_other' => ['nullable', 'required_if:offering_type,other', 'string', 'max:100'],
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

    /**
     * @return array<string, string>
     */
    protected function offeringMessages(): array
    {
        return [
            'offering_type_other.required_if' => 'Please specify the offering type.',
            'member_id.required_if' => 'Please select the member who gave this offering.',
            'church_service_id.required_if' => 'Please select the service where this general offering was collected.',
        ];
    }
}
