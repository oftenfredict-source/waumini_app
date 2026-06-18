<?php

namespace App\Http\Requests\Church;

use App\Enums\AttendanceSourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\AttendanceRecord::class);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $sourceType = $this->input('source_type');

        return [
            'source_type' => ['required', Rule::enum(AttendanceSourceType::class)],
            'source_id' => [
                'required',
                'integer',
                Rule::when(
                    $sourceType === AttendanceSourceType::ChurchService->value,
                    [Rule::exists('church_services', 'id')->where(fn ($q) => $q->where('church_id', $churchId))]
                ),
                Rule::when(
                    $sourceType === AttendanceSourceType::SpecialEvent->value,
                    [Rule::exists('special_events', 'id')->where(fn ($q) => $q->where('church_id', $churchId))]
                ),
            ],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => [
                'integer',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)->where('status', 'active')),
            ],
            'dependant_ids' => ['nullable', 'array'],
            'dependant_ids.*' => [
                'integer',
                Rule::exists('member_dependants', 'id')->where(fn ($q) => $q
                    ->where('church_id', $churchId)
                    ->where('relationship', 'child')
                    ->whereNull('linked_member_id')),
            ],
            'guests_count' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $memberIds = $this->input('member_ids', []);
            $dependantIds = $this->input('dependant_ids', []);
            $guests = (int) $this->input('guests_count', 0);

            if (empty($memberIds) && empty($dependantIds) && $guests === 0) {
                $validator->errors()->add('member_ids', 'Select at least one member, child, or guest.');
            }
        });
    }
}
