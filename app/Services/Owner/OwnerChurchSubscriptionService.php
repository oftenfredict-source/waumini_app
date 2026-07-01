<?php

namespace App\Services\Owner;

use App\Enums\ChurchStatus;
use App\Models\Church;
use App\Models\Payment;
use App\Models\SubscriptionPackage;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Church\ChurchSubscriptionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OwnerChurchSubscriptionService
{
    public function __construct(
        private readonly ChurchSubscriptionService $churchSubscriptionService,
        private readonly ChurchService $churchService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function activePackages(): Collection
    {
        return SubscriptionPackage::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @param  array{
     *     record_installation?: bool,
     *     record_yearly?: bool,
     *     installation_amount?: float|null,
     *     yearly_amount?: float|null,
     *     method?: string,
     *     provider_reference?: string|null,
     *     notes?: string|null,
     * }  $paymentInput
     */
    public function activatePaid(Church $church, SubscriptionPackage $package, array $paymentInput, User $actor): \App\Models\ChurchSubscription
    {
        return DB::transaction(function () use ($church, $package, $paymentInput, $actor) {
            $subscription = $this->churchSubscriptionService->upgrade($church->fresh(), $package);

            $this->recordPayments($church, $subscription, $package, $paymentInput);

            $this->auditLogService->log(
                'owner.church.subscription.activated',
                $church,
                null,
                [
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'subscription_id' => $subscription->id,
                    'owner_id' => $actor->id,
                    'payments_recorded' => array_filter([
                        ($paymentInput['record_installation'] ?? false) ? 'installation' : null,
                        ($paymentInput['record_yearly'] ?? false) ? 'yearly' : null,
                    ]),
                ],
                $church->id,
            );

            return $subscription->load('package');
        });
    }

    public function assignTrial(Church $church, SubscriptionPackage $package, User $actor): \App\Models\ChurchSubscription
    {
        return DB::transaction(function () use ($church, $package, $actor) {
            $subscription = $this->churchService->assignSubscription($church, $package, 'yearly');

            $church->update([
                'status' => ChurchStatus::Trial,
                'trial_ends_at' => $subscription->trial_ends_at,
            ]);

            $package->load('features');
            app(PackageFeatureService::class)->applyToChurch($church->fresh(), $package);

            $this->auditLogService->log(
                'owner.church.subscription.trial_assigned',
                $church,
                null,
                [
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'subscription_id' => $subscription->id,
                    'trial_ends_at' => $subscription->trial_ends_at?->toIso8601String(),
                    'owner_id' => $actor->id,
                ],
                $church->id,
            );

            return $subscription->load('package');
        });
    }

    /**
     * @param  array{
     *     record_installation?: bool,
     *     record_yearly?: bool,
     *     installation_amount?: float|null,
     *     yearly_amount?: float|null,
     *     method?: string,
     *     provider_reference?: string|null,
     *     notes?: string|null,
     * }  $paymentInput
     */
    private function recordPayments(
        Church $church,
        \App\Models\ChurchSubscription $subscription,
        SubscriptionPackage $package,
        array $paymentInput,
    ): void {
        $currency = strtoupper((string) ($package->currency ?: SystemSetting::platformCurrency()));
        $method = (string) ($paymentInput['method'] ?? 'cash');
        $reference = $paymentInput['provider_reference'] ?? null;
        $notes = $paymentInput['notes'] ?? null;
        $paidAt = now();

        if ($paymentInput['record_installation'] ?? false) {
            $this->createPayment($church, $subscription, [
                'amount' => (float) ($paymentInput['installation_amount'] ?? $package->installation_price),
                'currency' => $currency,
                'method' => $method,
                'provider_reference' => $reference,
                'paid_at' => $paidAt,
                'metadata' => [
                    'type' => 'installation',
                    'package' => $package->slug,
                    'notes' => $notes,
                ],
            ]);
        }

        if ($paymentInput['record_yearly'] ?? false) {
            $this->createPayment($church, $subscription, [
                'amount' => (float) ($paymentInput['yearly_amount'] ?? $package->yearly_price),
                'currency' => $currency,
                'method' => $method,
                'provider_reference' => $reference,
                'paid_at' => $paidAt,
                'metadata' => [
                    'type' => 'yearly',
                    'package' => $package->slug,
                    'notes' => $notes,
                ],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createPayment(Church $church, \App\Models\ChurchSubscription $subscription, array $data): Payment
    {
        return Payment::create([
            'church_id' => $church->id,
            'church_subscription_id' => $subscription->id,
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'method' => $data['method'],
            'provider' => 'manual',
            'provider_reference' => $data['provider_reference'] ?? null,
            'status' => 'completed',
            'paid_at' => $data['paid_at'] ?? now(),
            'metadata' => $data['metadata'] ?? null,
        ]);
    }
}
