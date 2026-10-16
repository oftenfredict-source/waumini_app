<?php

namespace App\Http\Requests\Church;

use App\Enums\FinancePaymentMethod;
use App\Enums\OfferingContributionType;
use App\Enums\OfferingType;
use App\Models\ChurchService;
use App\Services\Church\ChurchSettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBulkOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Offering::class);
    }

    public function rules(): array
    {
        $churchId = $this->user()->church_id;
        $customTypes = app(ChurchSettingsService::class)
            ->customOfferingTypes($this->user()->church);

        $allowedTypes = array_merge(
            array_map(fn (OfferingType $type) => $type->value, OfferingType::cases()),
            array_map(fn (string $label) => 'custom:'.$label, $customTypes),
        );

        $isMember = $this->input('contribution_type') === OfferingContributionType::Member->value;
        $isGeneral = $this->input('contribution_type') === OfferingContributionType::General->value;

        return [
            'contribution_type' => ['required', Rule::enum(OfferingContributionType::class)],
            'offering_date' => ['required', 'date', 'before_or_equal:today'],
            'offering_type' => ['required', 'string', Rule::in($allowedTypes)],
            'offering_type_other' => ['nullable', 'required_if:offering_type,other', 'string', 'max:100'],
            'payment_method' => ['required', Rule::enum(FinancePaymentMethod::class)],
            'notes' => ['nullable', 'string', 'max:1000'],

            'entries' => [$isMember ? 'required' : 'nullable', 'array', 'min:1', 'max:100'],
            'entries.*.member_id' => [
                Rule::requiredIf($isMember),
                'nullable',
                'distinct',
                Rule::exists('members', 'id')->where(fn ($q) => $q->where('church_id', $churchId)->where('status', 'active')),
            ],
            'entries.*.amount' => [Rule::requiredIf($isMember), 'nullable', 'numeric', 'min:0.01'],
            'entries.*.reference_number' => ['nullable', 'string', 'max:255'],

            'church_service_id' => [
                'nullable',
                Rule::requiredIf($isGeneral),
                Rule::exists('church_services', 'id')->where(fn ($q) => $q
                    ->where('church_id', $churchId)
                    ->where('status', '!=', 'cancelled')),
            ],
            'amount' => [Rule::requiredIf($isGeneral), 'nullable', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $method = $this->string('payment_method')->toString();
            $needsReference = in_array($method, ['bank_transfer', 'mobile_money', 'cheque'], true);
            $contributionType = $this->string('contribution_type')->toString();

            if ($needsReference && $contributionType === OfferingContributionType::Member->value) {
                foreach ($this->input('entries', []) as $index => $entry) {
                    if (trim((string) ($entry['reference_number'] ?? '')) === '') {
                        $validator->errors()->add(
                            "entries.{$index}.reference_number",
                            __('validation.required', ['attribute' => __('pages.shared.reference_number')])
                        );
                    }
                }
            }

            if ($needsReference && $contributionType === OfferingContributionType::General->value) {
                if (trim((string) $this->input('reference_number')) === '') {
                    $validator->errors()->add(
                        'reference_number',
                        __('validation.required', ['attribute' => __('pages.shared.reference_number')])
                    );
                }
            }

            if ($contributionType !== OfferingContributionType::General->value) {
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

            if (! $service || $service->canRecordAttendance()) {
                return;
            }

            $when = $service->attendanceOpensAt()?->format('M d, Y H:i')
                ?? __('pages.attendance.scheduled_start');

            $validator->errors()->add(
                'church_service_id',
                __('pages.offerings.service_not_yet_open', ['when' => $when])
            );
        });
    }

    public function messages(): array
    {
        return [
            'entries.required' => __('pages.offerings.entries_required'),
            'entries.min' => __('pages.offerings.entries_required'),
            'entries.*.member_id.distinct' => __('pages.offerings.duplicate_member'),
            'offering_type_other.required_if' => 'Please specify the offering type.',
            'church_service_id.required' => 'Please select the service where this general offering was collected.',
            'amount.required' => __('validation.required', ['attribute' => __('pages.shared.amount_tzs')]),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function offeringPayloads(): array
    {
        $shared = [
            'offering_date' => $this->string('offering_date')->toString(),
            'offering_type' => $this->string('offering_type')->toString(),
            'offering_type_other' => $this->input('offering_type_other'),
            'payment_method' => $this->string('payment_method')->toString(),
            'notes' => filled($this->input('notes')) ? trim((string) $this->input('notes')) : null,
        ];

        if ($this->input('contribution_type') === OfferingContributionType::General->value) {
            return [[
                ...$shared,
                'contribution_type' => OfferingContributionType::General->value,
                'church_service_id' => (int) $this->input('church_service_id'),
                'member_id' => null,
                'amount' => (float) $this->input('amount'),
                'reference_number' => filled($this->input('reference_number'))
                    ? trim((string) $this->input('reference_number'))
                    : null,
            ]];
        }

        return collect($this->validated('entries'))
            ->map(fn (array $entry) => [
                ...$shared,
                'contribution_type' => OfferingContributionType::Member->value,
                'member_id' => (int) $entry['member_id'],
                'church_service_id' => null,
                'amount' => (float) $entry['amount'],
                'reference_number' => filled($entry['reference_number'] ?? null)
                    ? trim((string) $entry['reference_number'])
                    : null,
            ])
            ->values()
            ->all();
    }
}
