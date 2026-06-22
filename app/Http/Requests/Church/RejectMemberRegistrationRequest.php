<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;

class RejectMemberRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('review', $this->route('registration'));
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
