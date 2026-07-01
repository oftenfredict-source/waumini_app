<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\ManageChurchSubscriptionRequest;
use App\Models\Church;
use App\Models\SubscriptionPackage;
use App\Services\Owner\OwnerChurchSubscriptionService;
use Illuminate\Http\RedirectResponse;

class ChurchSubscriptionController extends Controller
{
    public function __construct(
        private readonly OwnerChurchSubscriptionService $subscriptionService,
    ) {}

    public function store(ManageChurchSubscriptionRequest $request, Church $church): RedirectResponse
    {
        $package = SubscriptionPackage::query()
            ->where('is_active', true)
            ->findOrFail($request->integer('package_id'));

        if ($request->input('action') === 'assign_trial') {
            $this->subscriptionService->assignTrial($church, $package, $request->user());

            return back()->with('success', __('owner.church.subscription_trial_assigned', ['package' => $package->name]));
        }

        $this->subscriptionService->activatePaid(
            $church,
            $package,
            $request->paymentInput(),
            $request->user(),
        );

        return back()->with('success', __('owner.church.subscription_activated', ['package' => $package->name]));
    }
}
