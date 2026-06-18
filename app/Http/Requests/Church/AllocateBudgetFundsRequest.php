<?php

namespace App\Http\Requests\Church;

use Illuminate\Foundation\Http\FormRequest;

class AllocateBudgetFundsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('allocateFunds', $this->route('budget'));
    }

    public function rules(): array
    {
        return [
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
