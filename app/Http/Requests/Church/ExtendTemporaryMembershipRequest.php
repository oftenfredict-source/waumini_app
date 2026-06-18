<?php

namespace App\Http\Requests\Church;

use App\Enums\TemporaryDurationUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExtendTemporaryMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('member'));
    }

    public function rules(): array
    {
        return [
            'temporary_duration_value' => ['required', 'integer', 'min:1', 'max:99'],
            'temporary_duration_unit' => ['required', Rule::enum(TemporaryDurationUnit::class)],
        ];
    }
}
