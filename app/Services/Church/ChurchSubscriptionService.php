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

            if (in_array($church->status, [ChurchStatus::Trial, ChurchStatus::Pending], true)) {
                $church->update(['status' => ChurchStatus::Active]);
            }

            $package->load('features');
            $this->packageFeatureService->applyToChurch($church, $package);

            return $subscription->load('package');
        });
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
