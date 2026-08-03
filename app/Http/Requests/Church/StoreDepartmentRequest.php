<?php

namespace App\Http\Requests\Church;

use App\Enums\DepartmentStatus;
use App\Services\Church\BranchAccessService;
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
        $branchAccess = app(BranchAccessService::class);
        $branchesEnabled = $branchAccess->branchesFeatureEnabled($this->user());
        $branchId = $branchAccess->resolveBranchIdForCreate(
            $this->user(),
            $this->integer('branch_id') ?: null,
        );

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->where(
                    fn ($q) => $q->where('church_id', $churchId)
                        ->where('branch_id', $branchId)
                        ->whereNull('deleted_at')
                ),
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
            'branch_id' => $branchesEnabled ? [
                'nullable',
                'integer',
                Rule::exists('church_branches', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ] : ['nullable', 'prohibited'],
        ];
    }
}
