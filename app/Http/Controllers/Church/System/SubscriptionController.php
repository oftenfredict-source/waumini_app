<?php

namespace App\Http\Controllers\Church\System;

use App\Http\Requests\Church\UpgradeSubscriptionRequest;
use App\Models\SubscriptionPackage;
use App\Services\Church\ChurchSubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriptionController extends SystemController
{
    public function __construct(
        private readonly ChurchSubscriptionService $subscriptionService,
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->can('system.settings'), 403);

            return $next($request);
        })->except('terms');
    }

    public function index(): View
    {
        $church = $this->church()->load('activeSubscription.package.features');

        $currentSubscription = $church->activeSubscription;
        $currentPackage = $currentSubscription?->package;

        return view('church.system.subscription.index', [
            'church' => $church,
            'currentSubscription' => $currentSubscription,
            'currentPackage' => $currentPackage,
            'packages' => $this->subscriptionService->availablePackages($currentPackage),
            'currency' => $this->subscriptionService->currency(),
        ]);
    }

    public function upgrade(UpgradeSubscriptionRequest $request): RedirectResponse
    {
        $church = $this->church();
        $package = SubscriptionPackage::query()
            ->where('is_active', true)
            ->findOrFail($request->integer('package_id'));

        if ($church->activeSubscription?->package_id === $package->id) {
            return back()->with('info', 'You are already on this plan.');
        }

        $this->subscriptionService->upgrade($church, $package);

        return redirect()
            ->route('church.system.subscription.index')
            ->with('success', "Your plan has been upgraded to {$package->name}.");
    }

    public function terms(): View
    {
        abort_unless(auth()->user()?->isChurchPortalUser(), 403);

        return view('church.system.subscription.terms', [
            'church' => $this->church(),
            'termsHtml' => $this->subscriptionService->termsAndConditions(),
            'updatedAt' => \App\Models\SystemSetting::where('group', 'legal')
                ->where('key', 'terms_and_conditions')
                ->value('updated_at'),
        ]);
    }
}
