<?php

namespace App\Http\Requests\Api\Pastor;

use App\Enums\BereavementContributionType;
use App\Enums\BereavementPaymentMethod;
use App\Models\BereavementEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordPastorBereavementContributionRequest extends FormRequest
{
    private ?BereavementEvent $bereavementEvent = null;

    public function authorize(): bool
    {
        $event = $this->bereavementEvent();

        return $event !== null && $this->user()->can('manageContributions', $event);
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

    public function bereavementEvent(): ?BereavementEvent
    {
        if ($this->bereavementEvent !== null) {
            return $this->bereavementEvent;
        }

        $id = (int) $this->route('id');

        return $this->bereavementEvent = BereavementEvent::forChurch($this->user()->church_id)->find($id);
    }
}
