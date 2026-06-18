<?php

namespace App\Http\Requests\Church;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;

class MarkExpensePaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('markPaid', $this->route('expense'));
    }

    public function rules(): array
    {
        return [];
    }
}

