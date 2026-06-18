<?php

namespace App\Http\Requests\Church;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Department $department */
        $department = $this->route('department');

        return $this->user()->can('update', $department);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        /** @var Department $department */
        $department = $this->route('department');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')
                    ->where(fn ($q) => $q->where('church_id', $churchId))
                    ->ignore($department->id),
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
