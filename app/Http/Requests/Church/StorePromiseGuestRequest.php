<?php

namespace App\Http\Requests\Church;

use App\Enums\PromiseGuestType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePromiseGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\PromiseGuest::class);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;

        return [
            'guest_type' => ['required', Rule::enum(PromiseGuestType::class)],
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'promised_date' => ['required', 'date'],
            'event_link_type' => ['nullable', Rule::in(['church_service', 'special_event', 'none'])],
            'church_service_id' => [
                'nullable',
                'required_if:event_link_type,church_service',
                Rule::exists('church_services', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'special_event_id' => [
                'nullable',
                'required_if:event_link_type,special_event',
                Rule::exists('special_events', 'id')->where(fn ($q) => $q->where('church_id', $churchId)),
            ],
            'notes' => ['nullable', 'string', 'max:2000'],
            'send_sms' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'send_sms' => $this->boolean('send_sms'),
            'event_link_type' => $this->input('event_link_type', 'none'),
        ]);
    }
}
