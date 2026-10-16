<?php

namespace App\Http\Requests\Api\Pastor;

use Illuminate\Foundation\Http\FormRequest;

class DeactivatePastorLeaderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('leadership.manage') === true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:3', 'max:2000'],
        ];
    }
}
