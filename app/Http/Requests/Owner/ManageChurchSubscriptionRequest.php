<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManageChurchSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageSubscription', $this->route('church'));
    }

    public function rules(): array
    {
        $action = $this->input('action');

        $rules = [
            'action' => ['required', Rule::in(['activate', 'assign_trial'])],
            'package_id' => ['required', 'exists:subscription_packages,id'],
        ];

        if ($action === 'activate') {
            $rules = array_merge($rules, [
                'record_installation' => ['nullable', 'boolean'],
                'record_yearly' => ['nullable', 'boolean'],
                'installation_amount' => ['nullable', 'numeric', 'min:0'],
                'yearly_amount' => ['nullable', 'numeric', 'min:0'],
                'method' => ['nullable', 'string', Rule::in(['cash', 'bank_transfer', 'mobile_money', 'cheque', 'other'])],
                'provider_reference' => ['nullable', 'string', 'max:150'],
                'notes' => ['nullable', 'string', 'max:500'],
            ]);
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'record_installation' => $this->boolean('record_installation'),
            'record_yearly' => $this->boolean('record_yearly'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentInput(): array
    {
        return [
            'record_installation' => $this->boolean('record_installation'),
            'record_yearly' => $this->boolean('record_yearly'),
            'installation_amount' => $this->filled('installation_amount') ? (float) $this->input('installation_amount') : null,
            'yearly_amount' => $this->filled('yearly_amount') ? (float) $this->input('yearly_amount') : null,
            'method' => $this->input('method', 'cash'),
            'provider_reference' => $this->input('provider_reference'),
            'notes' => $this->input('notes'),
        ];
    }
}
