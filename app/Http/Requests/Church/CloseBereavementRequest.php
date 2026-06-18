<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;

class CloseBereavementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('close', $this->route('bereavement'));
    }

    public function rules(): array
    {
        return [
            'fund_usage' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
