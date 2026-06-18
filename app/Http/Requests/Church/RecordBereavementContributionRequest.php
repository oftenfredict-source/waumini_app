<?php

namespace App\Http\Requests\Church;

use App\Enums\BereavementContributionType;
use App\Enums\BereavementPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordBereavementContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageContributions', $this->route('bereavement'));
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'member_id' => [
                'required',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'contribution_date' => ['required', 'date'],
            'contribution_type' => ['required', Rule::enum(BereavementContributionType::class)],
            'payment_method' => ['required', Rule::enum(BereavementPaymentMethod::class)],
            'reference_number' => [
                'nullable',
                'required_if:payment_method,bank_transfer,mobile_money',
                'string',
                'max:255',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
