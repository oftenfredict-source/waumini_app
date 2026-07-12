<?php

namespace App\Http\Requests\Church;

use App\Enums\ChurchServiceStatus;
use App\Enums\ChurchServiceType;
use App\Enums\ServiceCoordinatorType;
use App\Enums\ServicePreacherType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChurchServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ChurchService::class);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $preacherType = $this->input('preacher_type');
        $coordinatorType = $this->input('coordinator_type');
        $branchAccess = app(\App\Services\Church\BranchAccessService::class);
        $user = $this->user();

        $rules = [
            'service_type' => ['required', Rule::enum(ChurchServiceType::class)],
            'title' => [
                'nullable',
                'required_if:service_type,extra',
                'string',
                'max:255',
            ],
            'service_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'theme' => ['nullable', 'string', 'max:255'],
            'preacher_type' => ['nullable', Rule::enum(ServicePreacherType::class)],
            'preacher_member_id' => [
                Rule::requiredIf(in_array($preacherType, [
                    ServicePreacherType::Pastor->value,
                    ServicePreacherType::Leader->value,
                    ServicePreacherType::Member->value,
                ], true)),
                'nullable',
                'integer',
                Rule::exists('members', 'id')->where(fn ($q) => $q
                    ->where('church_id', $churchId)
                    ->whereNull('deleted_at')),
            ],
            'preacher_guest_name' => [
                Rule::requiredIf($preacherType === ServicePreacherType::Guest->value),
                'nullable',
                'string',
                'max:255',
            ],
            'preacher_guest_phone' => [
                Rule::requiredIf($preacherType === ServicePreacherType::Guest->value),
                'nullable',
                'string',
                'max:30',
            ],
            'coordinator_type' => ['nullable', Rule::enum(ServiceCoordinatorType::class)],
            'coordinator_member_id' => [
                Rule::requiredIf($coordinatorType === ServiceCoordinatorType::Member->value),
                'nullable',
                'integer',
                Rule::exists('members', 'id')->where(fn ($q) => $q
                    ->where('church_id', $churchId)
                    ->whereNull('deleted_at')),
            ],
            'coordinator_guest_name' => [
                Rule::requiredIf($coordinatorType === ServiceCoordinatorType::Guest->value),
                'nullable',
                'string',
                'max:255',
            ],
            'coordinator_guest_phone' => [
                Rule::requiredIf($coordinatorType === ServiceCoordinatorType::Guest->value),
                'nullable',
                'string',
                'max:30',
            ],
            'venue' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ChurchServiceStatus::class)],
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
            'title.required_if' => 'Please enter a title for the extra service.',
            'preacher_member_id.required' => 'Please select the preacher / speaker.',
            'preacher_guest_name.required' => 'Please enter the special guest name.',
            'preacher_guest_phone.required' => 'Please enter the special guest phone number.',
            'coordinator_member_id.required' => 'Please select the coordinator.',
            'coordinator_guest_name.required' => 'Please enter the coordinator guest name.',
            'coordinator_guest_phone.required' => 'Please enter the coordinator guest phone number.',
        ];
    }
}
