<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpgradeSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('system.settings');
    }

    public function rules(): array
    {
        return [
            'package_id' => [
                'required',
                Rule::exists('subscription_packages', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
            'accept_terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'accept_terms.accepted' => 'You must accept the Terms & Conditions before upgrading your plan.',
        ];
    }
}
