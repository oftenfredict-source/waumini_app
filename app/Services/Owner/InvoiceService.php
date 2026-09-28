<?php

namespace App\Services\Owner;

use App\Models\Church;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function generate(Church $church, string $type, ?float $amount, User $actor, string $invoiceNumber): Invoice
    {
        if (! in_array($type, [Invoice::TYPE_INSTALLATION, Invoice::TYPE_YEARLY], true)) {
            throw new InvalidArgumentException('Invalid invoice type.');
        }

        $invoiceNumber = trim($invoiceNumber);
        if ($invoiceNumber === '') {
            throw new InvalidArgumentException('Invoice number is required.');
        }

        if (Invoice::where('invoice_number', $invoiceNumber)->exists()) {
            throw new InvalidArgumentException('Invoice number already exists.');
        }

        return DB::transaction(function () use ($church, $type, $amount, $actor, $invoiceNumber) {
            $church->loadMissing(['activeSubscription.package', 'adminUser']);

            $subscription = $church->activeSubscription;
            $package = $subscription?->package;
            $currency = SystemSetting::platformCurrency();
            $taxRate = (float) SystemSetting::getValue('billing', 'tax_rate', 0);
            $graceDays = (int) SystemSetting::getValue('billing', 'grace_period_days', 3);

            $unitPrice = $amount ?? $this->defaultAmount($type, $package);
            $quantity = 1;
            $subtotal = round($unitPrice * $quantity, 2);
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $totalAmount = round($subtotal + $taxAmount, 2);

            $description = $type === Invoice::TYPE_INSTALLATION
                ? 'Instalation cost'
                : 'Annual service cost';

            $issuedAt = now();
            $invoice = Invoice::create([
                'church_id' => $church->id,
                'church_subscription_id' => $subscription?->id,
                'invoice_number' => $invoiceNumber,
                'type' => $type,
                'description' => $description,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'currency' => $currency,
                'status' => Invoice::STATUS_PENDING,
                'issued_at' => $issuedAt,
                'due_at' => $issuedAt->copy()->addDays($graceDays),
                'recipient_name' => $this->recipientName($church),
                'recipient_phone' => $this->recipientPhone($church),
                'recipient_location' => $this->recipientLocation($church),
                'metadata' => [
                    'package_slug' => $package?->slug,
                    'package_name' => $package?->name,
                    'generated_by' => $actor->id,
                ],
            ]);

            $this->auditLogService->log(
                'owner.invoice.generated',
                $invoice,
                null,
                [
                    'invoice_number' => $invoice->invoice_number,
                    'type' => $type,
                    'total_amount' => $totalAmount,
                    'church_id' => $church->id,
                    'owner_id' => $actor->id,
                ],
                $church->id,
            );

            return $invoice->load(['church', 'subscription.package']);
        });
    }

    /**
     * @param  array{
     *     method?: string,
     *     provider_reference?: string|null,
     *     notes?: string|null,
     * }  $paymentInput
     */
    public function markPaid(Invoice $invoice, User $actor, array $paymentInput = []): Invoice
    {
        if (! $invoice->isPending()) {
            throw new InvalidArgumentException('Only pending invoices can be marked as paid.');
        }

        return DB::transaction(function () use ($invoice, $actor, $paymentInput) {
            $invoice->loadMissing(['church', 'subscription']);

            $method = (string) ($paymentInput['method'] ?? 'cash');
            $paidAt = now();

            $payment = Payment::create([
                'church_id' => $invoice->church_id,
                'church_subscription_id' => $invoice->church_subscription_id,
                'amount' => $invoice->total_amount,
                'currency' => $invoice->currency,
                'method' => $method,
                'provider' => 'manual',
                'provider_reference' => $paymentInput['provider_reference'] ?? null,
                'status' => 'completed',
                'paid_at' => $paidAt,
                'metadata' => [
                    'type' => $invoice->type,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'notes' => $paymentInput['notes'] ?? null,
                ],
            ]);

            $invoice->update([
                'status' => Invoice::STATUS_PAID,
                'payment_id' => $payment->id,
                'paid_at' => $paidAt,
            ]);

            $this->auditLogService->log(
                'owner.invoice.paid',
                $invoice,
                ['status' => Invoice::STATUS_PENDING],
                [
                    'status' => Invoice::STATUS_PAID,
                    'payment_id' => $payment->id,
                    'owner_id' => $actor->id,
                ],
                $invoice->church_id,
            );

            return $invoice->fresh(['church', 'subscription.package', 'payment']);
        });
    }

    private function defaultAmount(string $type, ?\App\Models\SubscriptionPackage $package): float
    {
        if (! $package) {
            return 0;
        }

        return $type === Invoice::TYPE_INSTALLATION
            ? (float) $package->installation_price
            : (float) $package->yearly_price;
    }

    private function recipientName(Church $church): string
    {
        return trim($church->pastor_name ?: ($church->adminUser?->name ?? ''));
    }

    private function recipientPhone(Church $church): ?string
    {
        return $church->adminUser?->phone ?: $church->phone;
    }

    private function recipientLocation(Church $church): ?string
    {
        $city = trim((string) $church->city);

        return $city !== '' ? strtoupper($city) : null;
    }
}
