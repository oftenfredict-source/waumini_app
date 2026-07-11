<?php

namespace App\Http\Requests\Church;

use App\Enums\FinancePaymentMethod;
use App\Enums\OfferingContributionType;
use App\Enums\OfferingType;
use App\Models\ChurchService;
use App\Services\Church\ChurchSettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class OfferingRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    protected function offeringRules(): array
    {
        $churchId = $this->user()->church_id;
        $customTypes = app(ChurchSettingsService::class)
            ->customOfferingTypes($this->user()->church);

        $allowedTypes = array_merge(
            array_map(fn (OfferingType $type) => $type->value, OfferingType::cases()),
            array_map(fn (string $label) => 'custom:'.$label, $customTypes),
        );

        return [
            'contribution_type' => ['required', Rule::enum(OfferingContributionType::class)],
            'member_id' => [
                'nullable',
                'required_if:contribution_type,member',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)->where('status', 'active')),
            ],
            'church_service_id' => [
                'nullable',
                'required_if:contribution_type,general',
                Rule::exists('church_services', 'id')->where(fn ($q) => $q
                    ->where('church_id', $churchId)
                    ->where('status', '!=', 'cancelled')),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'offering_date' => ['required', 'date', 'before_or_equal:today'],
            'offering_type' => ['required', 'string', Rule::in($allowedTypes)],
            'offering_type_other' => ['nullable', 'required_if:offering_type,other', 'string', 'max:100'],
            'payment_method' => ['required', Rule::enum(FinancePaymentMethod::class)],
            'reference_number' => [
                'nullable',
                'required_if:payment_method,bank_transfer,mobile_money,cheque',
                'string',
                'max:255',
            ],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('contribution_type') !== OfferingContributionType::General->value) {
                return;
            }

            $serviceId = (int) $this->input('church_service_id');
            if (! $serviceId || $validator->errors()->has('church_service_id')) {
                return;
            }

            $service = ChurchService::query()
                ->forChurch($this->user()->church_id)
                ->whereKey($serviceId)
                ->first();

            if (! $service) {
                return;
            }

            if (! $service->canRecordAttendance()) {
                $when = $service->attendanceOpensAt()?->format('M d, Y H:i')
                    ?? __('pages.attendance.scheduled_start');

                $validator->errors()->add(
                    'church_service_id',
                    __('pages.offerings.service_not_yet_open', ['when' => $when])
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function offeringMessages(): array
    {
        return [
            'offering_type_other.required_if' => 'Please specify the offering type.',
            'member_id.required_if' => 'Please select the member who gave this offering.',
            'church_service_id.required_if' => 'Please select the service where this general offering was collected.',
        ];
    }
}
