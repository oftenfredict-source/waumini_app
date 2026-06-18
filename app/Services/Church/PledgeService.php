<?php

namespace App\Services\Church;

use App\Enums\FinancialApprovalStatus;
use App\Enums\PledgePaymentFrequency;
use App\Enums\PledgeStatus;
use App\Enums\PledgeType;
use App\Models\Church;
use App\Models\Pledge;
use App\Models\PledgePayment;
use App\Models\User;
use Carbon\Carbon;

class PledgeService
{
    public function create(Church $church, array $data, ?User $recorder = null): Pledge
    {
        $data['church_id'] = $church->id;
        $data['recorded_by'] = $recorder?->id;
        $data['amount_paid'] = 0;
        $data['status'] = PledgeStatus::Active;
        $data = $this->normalizeTypeFields($data);
        $data['due_date'] = $this->resolveDueDate($data);

        unset($data['one_time_payment_date']);

        return Pledge::create($data);
    }

    public function update(Pledge $pledge, array $data): Pledge
    {
        unset($data['amount_paid'], $data['church_id'], $data['status']);

        $data = $this->normalizeTypeFields($data);
        $data['due_date'] = $this->resolveDueDate(array_merge($pledge->toArray(), $data));

        unset($data['one_time_payment_date']);

        $pledge->update($data);
        $pledge->refreshStatus();

        return $pledge->fresh();
    }

    public function delete(Pledge $pledge): void
    {
        $pledge->delete();
    }

    public function recordPayment(Pledge $pledge, array $data, ?User $recorder = null): PledgePayment
    {
        return PledgePayment::create([
            'pledge_id' => $pledge->id,
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => $recorder?->id,
            'approval_status' => FinancialApprovalStatus::Pending,
        ]);
    }

    public function applyApprovedPayment(PledgePayment $payment): void
    {
        $pledge = $payment->pledge;

        $pledge->update([
            'amount_paid' => (float) $pledge->amount_paid + (float) $payment->amount,
        ]);

        $pledge->refreshStatus();
    }

    private function normalizeTypeFields(array $data): array
    {
        if (($data['pledge_type'] ?? null) !== PledgeType::Other->value) {
            $data['pledge_type_other'] = null;
        }

        return $data;
    }

    private function resolveDueDate(array $data): ?string
    {
        $pledgeDate = Carbon::parse($data['pledge_date']);
        $frequency = $data['payment_frequency'] ?? PledgePaymentFrequency::Monthly->value;

        return match ($frequency) {
            PledgePaymentFrequency::Monthly->value => $pledgeDate->copy()->addMonth()->toDateString(),
            PledgePaymentFrequency::Quarterly->value => $pledgeDate->copy()->addMonths(3)->toDateString(),
            PledgePaymentFrequency::Annually->value => $pledgeDate->copy()->addYear()->toDateString(),
            PledgePaymentFrequency::OneTime->value => isset($data['one_time_payment_date'])
                ? Carbon::parse($data['one_time_payment_date'])->toDateString()
                : $pledgeDate->toDateString(),
            default => $pledgeDate->copy()->addMonth()->toDateString(),
        };
    }
}
