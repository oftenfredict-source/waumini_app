<?php

namespace App\Http\Requests\Church;

use App\Enums\DepartmentStatus;
use App\Models\Department;
use App\Services\Church\BranchAccessService;
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
        $branchAccess = app(BranchAccessService::class);
        $branchesEnabled = $branchAccess->branchesFeatureEnabled($this->user());
        $branchId = $department->branch_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')
                    ->where(fn ($q) => $q->where('church_id', $churchId)
                        ->where('branch_id', $branchId)
                        ->whereNull('deleted_at'))
                    ->ignore($department->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'head_id' => [
                'nullable',
                Rule::exists('members', 'id')->where(function ($q) use ($churchId, $branchId, $branchesEnabled) {
                    $q->where('church_id', $churchId);
                    if ($branchesEnabled && $branchId) {
                        $q->where('branch_id', $branchId);
                    }
                }),
            ],
            'status' => ['required', Rule::enum(DepartmentStatus::class)],
        ];
    }
}
