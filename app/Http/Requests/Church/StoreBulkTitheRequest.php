<?php

namespace App\Http\Requests\Church;

use App\Enums\FinancePaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBulkTitheRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Tithe::class);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'tithe_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['required', Rule::enum(FinancePaymentMethod::class)],
            'entries' => ['required', 'array', 'min:1', 'max:100'],
            'entries.*.member_id' => [
                'required',
                'distinct',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)->where('status', 'active')),
            ],
            'entries.*.amount' => ['required', 'numeric', 'min:0.01'],
            'entries.*.reference_number' => ['nullable', 'string', 'max:255'],
            'entries.*.notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $method = $this->string('payment_method')->toString();
            $needsReference = in_array($method, ['bank_transfer', 'mobile_money', 'cheque'], true);

            if (! $needsReference) {
                return;
            }

            foreach ($this->input('entries', []) as $index => $entry) {
                $reference = trim((string) ($entry['reference_number'] ?? ''));
                if ($reference === '') {
                    $validator->errors()->add(
                        "entries.{$index}.reference_number",
                        __('validation.required', ['attribute' => __('pages.shared.reference_number')])
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'entries.required' => __('pages.tithes.entries_required'),
            'entries.min' => __('pages.tithes.entries_required'),
            'entries.*.member_id.distinct' => __('pages.tithes.duplicate_member'),
        ];
    }

    /**
     * @return list<array{member_id:int,amount:float,tithe_date:string,payment_method:string,reference_number:?string,notes:?string}>
     */
    public function tithePayloads(): array
    {
        $date = $this->string('tithe_date')->toString();
        $method = $this->string('payment_method')->toString();

        return collect($this->validated('entries'))
            ->map(fn (array $entry) => [
                'member_id' => (int) $entry['member_id'],
                'amount' => (float) $entry['amount'],
                'tithe_date' => $date,
                'payment_method' => $method,
                'reference_number' => filled($entry['reference_number'] ?? null)
                    ? trim((string) $entry['reference_number'])
                    : null,
                'notes' => filled($entry['notes'] ?? null)
                    ? trim((string) $entry['notes'])
                    : null,
            ])
            ->values()
            ->all();
    }
}
