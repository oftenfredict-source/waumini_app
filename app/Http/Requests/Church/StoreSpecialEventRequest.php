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
        return [
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
        ];
    }

    public function messages(): array
    {
        return [
            'category_other.required_if' => 'Please specify the event category.',
        ];
    }
}
