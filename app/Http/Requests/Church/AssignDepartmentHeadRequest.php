<?php

namespace App\Http\Requests\Church;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignDepartmentHeadRequest extends FormRequest
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
            'head_id' => [
                'nullable',
                Rule::exists('members', 'id')->where(function ($q) use ($churchId, $department) {
                    $q->where('church_id', $churchId);
                    if ($department->branch_id) {
                        $q->where('branch_id', $department->branch_id);
                    }
                }),
            ],
        ];
    }
}
