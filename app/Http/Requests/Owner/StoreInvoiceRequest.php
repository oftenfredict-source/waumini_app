<?php

namespace App\Http\Requests\Owner;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Invoice::class);
    }

    public function rules(): array
    {
        $rules = [
            'invoice_number' => ['required', 'string', 'max:50', 'unique:invoices,invoice_number'],
            'type' => ['required', Rule::in([Invoice::TYPE_INSTALLATION, Invoice::TYPE_YEARLY])],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ];

        if (! $this->route('church')) {
            $rules['church_id'] = ['required', 'exists:churches,id'];
        }

        return $rules;
    }

    public function churchId(): int
    {
        if ($church = $this->route('church')) {
            return (int) $church->id;
        }

        return (int) $this->input('church_id');
    }

    public function amount(): ?float
    {
        return $this->filled('amount') ? (float) $this->input('amount') : null;
    }

    public function invoiceNumber(): string
    {
        return trim((string) $this->input('invoice_number'));
    }
}
