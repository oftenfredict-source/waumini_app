<?php

namespace App\Http\Requests\Church;

use App\Enums\ExpenseCategory;
use App\Enums\FinancePaymentMethod;
use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateExpenseRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $budget = Budget::forChurch($this->user()->church_id)
            ->whereKey($this->input('budget_id'))
            ->first();

        if ($budget) {
            $this->merge([
                'expense_name' => $budget->budget_name,
            ]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('expense'));
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'budget_id' => [
                'required',
                Rule::exists('budgets', 'id')->where(fn ($q) => $q
                    ->where('church_id', $churchId)
                    ->where('approval_status', 'approved')),
            ],
            'expense_category' => ['required', Rule::enum(ExpenseCategory::class)],
            'expense_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['required', Rule::enum(FinancePaymentMethod::class)],
            'reference_number' => [
                'nullable',
                'required_if:payment_method,bank_transfer,mobile_money,cheque',
                'string',
                'max:255',
            ],
            'vendor' => ['nullable', 'string', 'max:255'],
            'receipt_number' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $budget = Budget::forChurch($this->user()->church_id)
                ->where('approval_status', 'approved')
                ->whereKey($this->input('budget_id'))
                ->first();

            if (! $budget || ! is_numeric($this->input('amount'))) {
                return;
            }

            $remaining = max(0, (float) $budget->total_budget - (float) $budget->spent_amount);

            if ((float) $this->input('amount') > $remaining) {
                $validator->errors()->add(
                    'amount',
                    'The expense amount cannot exceed the remaining budget amount of TZS '.number_format($remaining, 2).'.'
                );
            }
        });
    }
}
