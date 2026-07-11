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
            'is_kipaimara' => ['nullable', 'boolean'],
            'kipaimara_date' => ['nullable', 'date', 'before_or_equal:today'],
            'kipaimara_place' => ['nullable', 'string', 'max:255'],
            'kipaimara_by' => ['nullable', 'string', 'max:255'],
            'is_student' => ['nullable', 'boolean'],
            'education_level' => ['nullable', 'required_if:is_student,1', Rule::enum(\App\Enums\ChildEducationLevel::class)],
            'school_name' => ['nullable', 'required_if:is_student,1', 'string', 'max:255'],
            'school_region' => ['nullable', 'required_if:is_student,1', 'string', 'max:100'],
            'school_district' => ['nullable', 'required_if:is_student,1', 'string', 'max:100'],
            'school_ward' => ['nullable', 'string', 'max:100'],
            'school_street' => ['nullable', 'string', 'max:150'],
        ];
    }
}
