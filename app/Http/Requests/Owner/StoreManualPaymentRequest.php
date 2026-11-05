<?php

namespace App\Http\Requests\Owner;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Payment::class);
    }

    public function rules(): array
    {
        $hasInvoice = filled($this->input('invoice_id'));

        return [
            'church_id' => ['required', 'integer', 'exists:churches,id'],
            'invoice_id' => [
                'nullable',
                'integer',
                Rule::exists('invoices', 'id')->where(function ($query) {
                    $query->where('church_id', (int) $this->input('church_id'))
                        ->where('status', 'pending');
                }),
            ],
            'amount' => [$hasInvoice ? 'nullable' : 'required', 'numeric', 'min:0.01'],
            'type' => ['nullable', Rule::in(['installation', 'yearly', 'other'])],
            'method' => ['required', Rule::in(['cash', 'bank_transfer', 'mobile_money', 'cheque', 'other'])],
            'provider_reference' => ['nullable', 'string', 'max:150'],
            'paid_at' => ['nullable', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentInput(): array
    {
        return [
            'church_id' => (int) $this->input('church_id'),
            'invoice_id' => $this->filled('invoice_id') ? (int) $this->input('invoice_id') : null,
            'amount' => $this->filled('amount') ? (float) $this->input('amount') : null,
            'type' => $this->input('type', 'other'),
            'method' => $this->input('method', 'cash'),
            'provider_reference' => $this->input('provider_reference'),
            'paid_at' => $this->input('paid_at'),
            'notes' => $this->input('notes'),
        ];
    }
}
