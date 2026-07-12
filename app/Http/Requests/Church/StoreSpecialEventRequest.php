<?php

namespace App\Http\Requests\Church;

use App\Enums\SpecialEventCategory;
use App\Enums\SpecialEventStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSpecialEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\SpecialEvent::class);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $branchAccess = app(\App\Services\Church\BranchAccessService::class);
        $user = $this->user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(SpecialEventCategory::class)],
            'category_other' => ['nullable', 'required_if:category,other', 'string', 'max:100'],
            'event_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'speaker' => ['nullable', 'string', 'max:255'],
            'venue' => ['nullable', 'string', 'max:255'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'expected_attendance' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(SpecialEventStatus::class)],
            'description' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'branch_id' => ['nullable', 'integer'],
        ];

        if (
            $branchAccess->branchesFeatureEnabled($user)
            && $branchAccess->managesAllBranches($user)
            && ! $branchAccess->sessionBranchId($user)
        ) {
            $rules['branch_id'] = [
                'required',
                'integer',
                Rule::exists('church_branches', 'id')->where(fn ($q) => $q
                    ->where('church_id', $churchId)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'category_other.required_if' => 'Please specify the event category.',
        ];
    }
}
