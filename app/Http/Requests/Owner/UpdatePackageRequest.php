<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('subscriptions.manage');
    }

    public function rules(): array
    {
        $packageId = $this->route('package')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', 'alpha_dash', Rule::unique('subscription_packages', 'slug')->ignore($packageId)],
            'description' => ['nullable', 'string', 'max:500'],
            'installation_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'trial_days' => ['required', 'integer', 'min:0', 'max:90'],
            'max_members' => ['nullable', 'integer', 'min:1'],
            'max_sms_monthly' => ['nullable', 'integer', 'min:0'],
            'max_storage_mb' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'features.*' => ['boolean'],
        ];
    }
}
