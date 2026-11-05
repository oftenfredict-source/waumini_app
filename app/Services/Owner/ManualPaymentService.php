<?php

namespace App\Services\Owner;

use App\Models\Church;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Church\ChurchSubscriptionService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ManualPaymentService
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly ChurchSubscriptionService $subscriptionService,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array{
     *     church_id: int,
     *     amount?: float|null,
     *     method?: string,
     *     type?: string,
     *     invoice_id?: int|null,
     *     provider_reference?: string|null,
     *     paid_at?: string|null,
     *     notes?: string|null,
     * }  $input
     */
    public function record(array $input, User $actor): Payment
    {
        $church = Church::query()->findOrFail($input['church_id']);
        $invoiceId = $input['invoice_id'] ?? null;

        if ($invoiceId) {
            $invoice = Invoice::query()
                ->where('church_id', $church->id)
                ->findOrFail($invoiceId);

            if (! $invoice->isPending()) {
                throw new InvalidArgumentException('Only pending invoices can be marked as paid.');
            }

            $invoice = $this->invoiceService->markPaid($invoice, $actor, [
                'method' => $input['method'] ?? 'cash',
                'provider_reference' => $input['provider_reference'] ?? null,
                'notes' => $input['notes'] ?? null,
            ]);

            return $invoice->payment;
        }

        return DB::transaction(function () use ($church, $input, $actor) {
            $amount = (float) ($input['amount'] ?? 0);
            if ($amount <= 0) {
                throw new InvalidArgumentException('Payment amount must be greater than zero.');
            }

            $paidAt = ! empty($input['paid_at'])
                ? \Carbon\Carbon::parse($input['paid_at'])->endOfDay()
                : now();

            $type = (string) ($input['type'] ?? 'other');
            $method = (string) ($input['method'] ?? 'cash');

            $subscription = null;
            if ($this->subscriptionService->shouldActivateFromPaymentType($type)) {
                $subscription = $this->subscriptionService->renewFromPayment($church);
            } else {
                $church->loadMissing('activeSubscription');
                $subscription = $church->activeSubscription;
            }

            $payment = Payment::create([
                'church_id' => $church->id,
                'church_subscription_id' => $subscription?->id,
                'amount' => $amount,
                'currency' => SystemSetting::platformCurrency(),
                'method' => $method,
                'provider' => 'manual',
                'provider_reference' => $input['provider_reference'] ?? null,
                'status' => 'completed',
                'paid_at' => $paidAt,
                'metadata' => [
                    'type' => $type,
                    'covers' => $type === 'installation' ? 'first_year' : ($type === 'yearly' ? 'renewal_year' : null),
                    'notes' => $input['notes'] ?? null,
                    'recorded_by' => $actor->id,
                    'source' => 'owner_manual',
                    'subscription_activated' => $subscription !== null
                        && $this->subscriptionService->shouldActivateFromPaymentType($type),
                ],
            ]);

            $this->auditLogService->log(
                'owner.payment.manual_recorded',
                $payment,
                null,
                [
                    'church_id' => $church->id,
                    'amount' => $amount,
                    'method' => $method,
                    'type' => $type,
                    'subscription_id' => $subscription?->id,
                    'owner_id' => $actor->id,
                ],
                $church->id,
            );

            return $payment->load(['church', 'subscription.package']);
        });
    }
}
