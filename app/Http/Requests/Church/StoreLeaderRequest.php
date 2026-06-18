<?php

namespace App\Http\Requests\Church;

use App\Enums\LeadershipPosition;
use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('leadership.manage');
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'member_id' => [
                'required',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'position' => ['required', Rule::enum(LeadershipPosition::class)],
            'position_title' => [
                Rule::requiredIf(fn () => $this->input('position') === LeadershipPosition::Other->value),
                'nullable',
                'string',
                'max:255',
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'appointment_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:appointment_date'],
            'appointed_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
