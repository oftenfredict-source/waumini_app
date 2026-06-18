<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChurchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('churches.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('churches', 'slug')],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'denomination' => ['nullable', 'string', 'max:150'],
            'pastor_name' => ['nullable', 'string', 'max:150'],
            'package_id' => ['nullable', 'exists:subscription_packages,id'],
            'billing_cycle' => ['nullable', Rule::in(['yearly'])],
            'timezone' => ['nullable', 'string', 'max:50'],
            'currency' => ['nullable', 'string', Rule::in(array_keys(config('currencies')))],
            'admin_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'branches_enabled' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'branches_enabled' => $this->boolean('branches_enabled'),
        ]);

        if (! $this->filled('admin_email') && $this->filled('email')) {
            $this->merge(['admin_email' => $this->email]);
        }
    }
}
