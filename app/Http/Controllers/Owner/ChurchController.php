<?php

namespace App\Http\Controllers\Owner;

use App\Enums\ChurchStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreChurchRequest;
use App\Http\Requests\Owner\UpdateChurchRequest;
use App\Models\Church;
use App\Models\Payment;
use App\Models\SubscriptionPackage;
use App\Models\SystemSetting;
use App\Services\Owner\ChurchImpersonationService;
use App\Services\Owner\ChurchService;
use App\Services\Owner\OwnerChurchSubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChurchController extends Controller
{
    public function __construct(
        private readonly ChurchService $churchService,
        private readonly ChurchImpersonationService $impersonationService,
        private readonly OwnerChurchSubscriptionService $subscriptionService,
    ) {
        $this->authorizeResource(Church::class, 'church');
    }

    public function index(Request $request): View
    {
        $query = Church::with(['activeSubscription.package', 'primaryDomain', 'adminUser'])
            ->latest();

        if ($search = $request->string('search')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->string('status')->trim()->toString()) {
            $query->where('status', $status);
        }

        $churches = $query->paginate(15)->withQueryString();

        return view('owner.churches.index', [
            'churches' => $churches,
            'statuses' => ChurchStatus::cases(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Church::class);

        return view('owner.churches.create', [
            'packages' => SubscriptionPackage::where('is_active', true)->orderBy('sort_order')->get(),
            'defaultCurrency' => SystemSetting::platformCurrency(),
            'platformCurrency' => SystemSetting::platformCurrency(),
        ]);
    }

    public function store(StoreChurchRequest $request): RedirectResponse
    {
        $package = $request->filled('package_id')
            ? SubscriptionPackage::find($request->package_id)
            : SubscriptionPackage::where('slug', 'basic')->first();

        $result = $this->churchService->create($request->validated(), $package);
        $church = $result['church'];

        return redirect()
            ->route('owner.churches.show', $church)
            ->with('success', 'Church created successfully.')
            ->with('admin_credentials', [
                'email' => $church->adminUser->email,
                'password' => $result['admin_password'],
            ]);
    }

    public function show(Church $church): View
    {
        $church->load(['domains', 'subscriptions.package', 'activeSubscription.package', 'adminUser']);

        return view('owner.churches.show', [
            'church' => $church,
            'packages' => $this->subscriptionService->activePackages(),
            'recentPayments' => Payment::query()
                ->where('church_id', $church->id)
                ->latest('paid_at')
                ->limit(10)
                ->get(),
            'platformCurrency' => SystemSetting::platformCurrency(),
        ]);
    }

    public function edit(Church $church): View
    {
        return view('owner.churches.edit', [
            'church' => $church,
            'defaultCurrency' => SystemSetting::defaultChurchCurrency(),
        ]);
    }

    public function update(UpdateChurchRequest $request, Church $church): RedirectResponse
    {
        $this->churchService->update($church, $request->validated());

        return redirect()
            ->route('owner.churches.show', $church)
            ->with('success', 'Church updated successfully.');
    }

    public function destroy(Church $church): RedirectResponse
    {
        $this->churchService->delete($church);

        return redirect()
            ->route('owner.churches.index')
            ->with('success', 'Church deleted successfully.');
    }

    public function suspend(Request $request, Church $church): RedirectResponse
    {
        $this->authorize('suspend', $church);

        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $this->churchService->suspend($church, $request->reason);

        return back()->with('success', 'Church suspended successfully.');
    }

    public function activate(Church $church): RedirectResponse
    {
        $this->authorize('activate', $church);

        $this->churchService->activate($church);

        return back()->with('success', 'Church activated successfully.');
    }

    public function regenerateAdminPassword(Church $church): RedirectResponse
    {
        $this->authorize('manageAdmin', $church);

        $plainPassword = $this->churchService->regenerateAdminPassword($church);
        $church->load('adminUser');

        return back()
            ->with('success', 'Church admin password reset successfully.')
            ->with('admin_credentials', [
                'email' => $church->adminUser->email,
                'password' => $plainPassword,
            ]);
    }

    public function createAdmin(Church $church): RedirectResponse
    {
        $this->authorize('manageAdmin', $church);

        $plainPassword = $this->churchService->createMissingAdmin($church);
        $church->load('adminUser');

        return back()
            ->with('success', 'Church admin account created successfully.')
            ->with('admin_credentials', [
                'email' => $church->adminUser->email,
                'password' => $plainPassword,
            ]);
    }

    public function impersonate(Request $request, Church $church): RedirectResponse
    {
        $this->authorize('impersonate', $church);

        return $this->impersonationService->start($request->user(), $church, $request);
    }
}
