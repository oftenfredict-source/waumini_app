<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertChildToMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('convert', $this->route('dependant'));
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'envelope_number' => [
                'required',
                'string',
                'digits:3',
                Rule::unique('members', 'envelope_number')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'phone_number' => ['nullable', 'string', 'max:30'],
        ];
    }
}
