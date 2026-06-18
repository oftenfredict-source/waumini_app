<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;

class SendManualSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('system.settings') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }
}
