<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDependantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $dependant = $this->route('dependant');

        return $dependant && $this->user()->can('update', $dependant);
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'relationship_note' => ['nullable', 'string', 'max:150'],
            'is_baptized' => ['nullable', 'boolean'],
            'baptism_date' => ['nullable', 'date', 'before_or_equal:today'],
            'baptism_place' => ['nullable', 'string', 'max:255'],
            'baptized_by' => ['nullable', 'string', 'max:255'],
        ];
    }
}
