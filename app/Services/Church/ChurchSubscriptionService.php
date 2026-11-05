<?php

namespace App\Services\Church;

use App\Enums\BillingCycle;
use App\Enums\ChurchStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Church;
use App\Models\ChurchSubscription;
use App\Models\SubscriptionPackage;
use App\Models\SystemSetting;
use App\Services\Owner\PackageFeatureService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChurchSubscriptionService
{
    public function __construct(
        private readonly PackageFeatureService $packageFeatureService,
    ) {}

    public function availablePackages(?SubscriptionPackage $currentPackage = null): Collection
    {
        return SubscriptionPackage::query()
            ->where('is_active', true)
            ->with('features')
            ->orderBy('sort_order')
            ->get()
            ->map(function (SubscriptionPackage $package) use ($currentPackage) {
                $package->setAttribute('is_current', $currentPackage?->id === $package->id);
                $package->setAttribute('is_upgrade', $currentPackage
                    ? $package->sort_order > $currentPackage->sort_order
                    : false);

                return $package;
            });
    }

    public function upgrade(Church $church, SubscriptionPackage $package): ChurchSubscription
    {
        return DB::transaction(function () use ($church, $package) {
            $church->subscriptions()
                ->whereIn('status', [
                    SubscriptionStatus::Trial->value,
                    SubscriptionStatus::Active->value,
                    SubscriptionStatus::PastDue->value,
                    SubscriptionStatus::Expired->value,
                ])
                ->update([
                    'status' => SubscriptionStatus::Cancelled->value,
                    'cancelled_at' => now(),
                ]);

            $startsAt = now();
            $endsAt = $startsAt->copy()->addYear();

            $subscription = $church->subscriptions()->create([
                'package_id' => $package->id,
                'billing_cycle' => BillingCycle::Yearly,
                'status' => SubscriptionStatus::Active,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'trial_ends_at' => null,
                'auto_renew' => true,
            ]);

            if (in_array($church->status, [
                ChurchStatus::Trial,
                ChurchStatus::Pending,
                ChurchStatus::Expired,
                ChurchStatus::Suspended,
            ], true)) {
                $settings = $church->settings ?? [];
                unset($settings['trial_expired_notified_at'], $settings['trial_reminder_sent_on']);

                $church->update([
                    'status' => ChurchStatus::Active,
                    'suspended_at' => null,
                    'suspended_reason' => null,
                    'trial_ends_at' => null,
                    'settings' => $settings,
                ]);
            }

            $package->load('features');
            $this->packageFeatureService->applyToChurch($church->fresh(), $package);

            return $subscription->load('package');
        });
    }

    /**
     * Renew/activate subscription after a subscription payment (e.g. expired church pays).
     */
    public function renewFromPayment(Church $church, ?SubscriptionPackage $package = null): ChurchSubscription
    {
        $package ??= $this->resolvePackageForRenewal($church);

        if (! $package) {
            throw new \InvalidArgumentException('No subscription package found to activate for this church.');
        }

        return $this->upgrade($church->fresh(), $package);
    }

    public function resolvePackageForRenewal(Church $church): ?SubscriptionPackage
    {
        $church->loadMissing(['activeSubscription.package']);

        if ($church->activeSubscription?->package) {
            return $church->activeSubscription->package;
        }

        return $church->subscriptions()
            ->with('package')
            ->latest('id')
            ->first()
            ?->package;
    }

    public function shouldActivateFromPaymentType(?string $type): bool
    {
        return in_array($type, ['yearly', 'installation'], true);
    }

    public function termsAndConditions(): string
    {
        return (string) SystemSetting::getValue(
            'legal',
            'terms_and_conditions',
            config('legal.terms_and_conditions'),
        );
    }

    public function currency(): string
    {
        return SystemSetting::platformCurrency();
    }
}
