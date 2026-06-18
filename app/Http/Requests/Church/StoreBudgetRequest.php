<?php

namespace App\Http\Requests\Church;

use App\Enums\BudgetStatus;
use App\Enums\BudgetType;
use App\Enums\OfferingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Budget::class);
    }

    public function rules(): array
    {
        return [
            'budget_name' => ['required', 'string', 'max:255'],
            'budget_type' => ['required', Rule::enum(BudgetType::class)],
            'purpose' => ['nullable', 'string', 'max:255'],
            'primary_offering_type' => ['nullable', 'string', 'max:50'],
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'total_budget' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', Rule::enum(BudgetStatus::class)],
            'line_items' => ['nullable', 'array'],
            'line_items.*.item_name' => ['nullable', 'string', 'max:255'],
            'line_items.*.amount' => ['nullable', 'numeric', 'min:0'],
            'line_items.*.responsible_person' => ['nullable', 'string', 'max:255'],
            'line_items.*.notes' => ['nullable', 'string', 'max:500'],
            'funding_allocations' => ['nullable', 'array'],
            'funding_allocations.*' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
