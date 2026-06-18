<?php

namespace App\Http\Requests\Church;

use App\Enums\DepartmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('departments.manage');
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'head_id' => [
                'nullable',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'status' => ['required', Rule::enum(DepartmentStatus::class)],
        ];
    }
}
