<?php

namespace App\Services\Owner;

use App\Models\Church;
use App\Models\SubscriptionPackage;
use App\Services\Church\BranchService;

class PackageFeatureService
{
    public function __construct(
        private readonly BranchService $branchService,
    ) {}

    public function applyToChurch(Church $church, SubscriptionPackage $package): void
    {
        $package->loadMissing('features');

        $branchesEnabled = $package->hasFeature('branches');

        if ($church->branches_enabled === $branchesEnabled) {
            if ($branchesEnabled) {
                $this->branchService->ensureHeadquartersBranch($church);
            }

            return;
        }

        $church->update(['branches_enabled' => $branchesEnabled]);

        if ($branchesEnabled) {
            $this->branchService->ensureHeadquartersBranch($church);
        }
    }

    public function applyToSubscribedChurches(SubscriptionPackage $package): void
    {
        Church::query()
            ->whereHas('activeSubscription', fn ($query) => $query->where('package_id', $package->id))
            ->with('activeSubscription.package.features')
            ->each(fn (Church $church) => $this->applyToChurch($church, $package));
    }
}
