<?php

namespace App\Http\Requests\Owner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChurchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('churches.update');
    }

    public function rules(): array
    {
        $churchId = $this->route('church')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'denomination' => ['nullable', 'string', 'max:150'],
            'pastor_name' => ['nullable', 'string', 'max:150'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'currency' => ['nullable', 'string', Rule::in(array_keys(config('currencies')))],
            'branches_enabled' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'branches_enabled' => $this->boolean('branches_enabled'),
        ]);
    }
}
