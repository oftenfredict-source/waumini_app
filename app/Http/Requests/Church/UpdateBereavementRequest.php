<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBereavementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('bereavement'));
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'deceased_name' => ['required', 'string', 'max:255'],
            'affected_member_id' => [
                'nullable',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'family_details' => ['nullable', 'string', 'max:5000'],
            'related_departments' => ['nullable', 'string', 'max:500'],
            'incident_date' => ['required', 'date'],
            'contribution_start_date' => ['required', 'date'],
            'contribution_end_date' => ['required', 'date', 'after_or_equal:contribution_start_date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'fund_usage' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
