<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChildRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\MemberDependant::class);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'parent_type' => ['required', Rule::in(['member', 'non_member'])],
            'member_id' => [
                'required_if:parent_type,member',
                'nullable',
                'integer',
                Rule::exists('members', 'id')->where(fn ($q) => $q
                    ->where('church_id', $churchId)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')),
            ],
            'guardian_full_name' => ['required_if:parent_type,non_member', 'nullable', 'string', 'max:255'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'relationship_note' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required_if' => 'Please select the parent church member.',
            'guardian_full_name.required_if' => 'Please enter the guardian full name.',
        ];
    }
}
