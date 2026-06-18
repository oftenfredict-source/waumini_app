<?php

namespace App\Http\Requests\Church;

use App\Enums\ChurchServiceStatus;
use App\Enums\ChurchServiceType;
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
        return [
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
            'preacher' => ['nullable', 'string', 'max:255'],
            'venue' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(ChurchServiceStatus::class)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required_if' => 'Please enter a title for the extra service.',
        ];
    }
}
