<?php

namespace App\Http\Requests\Api\Pastor;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachPastorDepartmentMembersRequest extends FormRequest
{
    private ?Department $departmentRecord = null;

    public function authorize(): bool
    {
        $department = $this->department();

        return $department instanceof Department && $this->user()->can('update', $department);
    }

    public function department(): ?Department
    {
        if ($this->departmentRecord) {
            return $this->departmentRecord;
        }

        $id = (int) $this->route('id');
        if ($id < 1 || ! $this->user()?->church_id) {
            return null;
        }

        return $this->departmentRecord = Department::forChurch($this->user()->church_id)->find($id);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $department = $this->department();

        return [
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => [
                'integer',
                Rule::exists('members', 'id')->where(function ($q) use ($churchId, $department) {
                    $q->where('church_id', $churchId);
                    if ($department?->branch_id) {
                        $q->where('branch_id', $department->branch_id);
                    }
                }),
            ],
        ];
    }
}
