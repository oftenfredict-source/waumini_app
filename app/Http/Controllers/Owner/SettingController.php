<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StorePackageRequest;
use App\Http\Requests\Owner\UpdatePackageRequest;
use App\Models\DatabaseBackupLog;
use App\Models\Feature;
use App\Models\SubscriptionPackage;
use App\Models\SystemSetting;
use App\Services\Owner\AuditLogService;
use App\Services\Owner\DatabaseBackupService;
use App\Services\Owner\PackageFeatureService;
use App\Services\Sms\ChurchSmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly PackageFeatureService $packageFeatureService,
        private readonly ChurchSmsService $churchSmsService,
        private readonly DatabaseBackupService $databaseBackupService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', SystemSetting::class);

        return view('owner.settings.index', [
            'settings' => $this->allSettings(),
            'packages' => SubscriptionPackage::with('features')
                ->withCount('subscriptions')
                ->orderBy('sort_order')
                ->get(),
            'features' => Feature::orderBy('module')->orderBy('name')->get(),
            'baseDomain' => config('waumini.base_domain'),
            'backupSettings' => $this->databaseBackupService->settings(),
            'backupLogs' => DatabaseBackupLog::query()->latest()->limit(10)->get(),
        ]);
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'support_email' => ['required', 'email'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'email_from_name' => ['nullable', 'string', 'max:100'],
            'email_from_address' => ['nullable', 'email'],
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::setValue('general', $key, $value);
        }

        return $this->redirectToTab('general', 'General settings saved.');
    }

    public function updateBilling(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $data = $request->validate([
            'currency' => ['required', 'string', Rule::in(array_keys(config('currencies')))],
            'trial_days' => ['required', 'integer', 'min:0', 'max:90'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'grace_period_days' => ['nullable', 'integer', 'min:0', 'max:30'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_tin' => ['nullable', 'string', 'max:50'],
            'company_website' => ['nullable', 'string', 'max:150'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_name' => ['nullable', 'string', 'max:150'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'invoice_purpose' => ['nullable', 'string', 'max:255'],
            'invoice_recipient_title' => ['nullable', 'string', 'max:100'],
        ]);

        SystemSetting::setValue('billing', 'currency', strtoupper($data['currency']));
        SystemSetting::setValue('billing', 'trial_days', (int) $data['trial_days']);
        SystemSetting::setValue('billing', 'tax_rate', (float) ($data['tax_rate'] ?? 0));
        SystemSetting::setValue('billing', 'grace_period_days', (int) ($data['grace_period_days'] ?? 3));
        SystemSetting::setValue('churches', 'default_currency', strtoupper($data['currency']));

        foreach ([
            'company_name', 'company_address', 'company_phone', 'company_tin', 'company_website',
            'bank_name', 'bank_account_name', 'bank_account_number', 'invoice_purpose', 'invoice_recipient_title',
        ] as $key) {
            SystemSetting::setValue('billing', $key, $data[$key] ?? '');
        }

        SubscriptionPackage::query()->update(['currency' => strtoupper($data['currency'])]);

        return $this->redirectToTab('billing', 'Billing settings saved.');
    }

    public function updateChurches(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $data = $request->validate([
            'allow_registration' => ['nullable', 'boolean'],
            'require_approval' => ['nullable', 'boolean'],
            'default_timezone' => ['required', 'string', 'max:50'],
            'default_country' => ['nullable', 'string', 'max:100'],
            'default_currency' => ['required', 'string', Rule::in(array_keys(config('currencies')))],
        ]);

        SystemSetting::setValue('churches', 'allow_registration', $request->boolean('allow_registration'));
        SystemSetting::setValue('churches', 'require_approval', $request->boolean('require_approval'));
        SystemSetting::setValue('churches', 'default_timezone', $data['default_timezone']);
        SystemSetting::setValue('churches', 'default_country', $data['default_country'] ?? '');
        SystemSetting::setValue('churches', 'default_currency', strtoupper($data['default_currency']));

        return $this->redirectToTab('churches', 'Church settings saved.');
    }

    public function updateNotifications(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $data = $request->validate([
            'sms_enabled' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
            'expiry_reminder_days' => ['nullable', 'string', 'max:50'],
            'sms_api_url' => ['nullable', 'url', 'max:255'],
            'sms_username' => ['nullable', 'string', 'max:100'],
            'sms_password' => ['nullable', 'string', 'max:100'],
            'sms_sender_id' => ['nullable', 'string', 'max:20'],
        ]);

        SystemSetting::setValue('notifications', 'sms_enabled', $request->boolean('sms_enabled'));
        SystemSetting::setValue('notifications', 'email_notifications', $request->boolean('email_notifications'));
        SystemSetting::setValue('notifications', 'expiry_reminder_days', $data['expiry_reminder_days'] ?? '7,3,1');

        if (! empty($data['sms_api_url'])) {
            SystemSetting::setValue('sms', 'api_url', $data['sms_api_url']);
        }

        if (! empty($data['sms_username'])) {
            SystemSetting::setValue('sms', 'username', $data['sms_username']);
        }

        if ($request->filled('sms_password')) {
            SystemSetting::setValue('sms', 'password', $request->input('sms_password'));
        }

        if (! empty($data['sms_sender_id'])) {
            SystemSetting::setValue('sms', 'sender_id', $data['sms_sender_id']);
        }

        return $this->redirectToTab('notifications', 'Notification settings saved.');
    }

    public function testSms(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $data = $request->validate([
            'test_phone' => ['required', 'string', 'max:30'],
            'test_message' => ['nullable', 'string', 'max:160'],
        ]);

        $message = $data['test_message'] ?? 'HABARI - Waumini Link SMS test';
        $result = $this->churchSmsService->sendTest($data['test_phone'], $message);

        if ($result['ok'] ?? false) {
            return $this->redirectToTab('notifications', 'Test SMS sent successfully.');
        }

        $reason = $result['reason'] ?? $result['body'] ?? $result['error'] ?? 'Unknown error';

        return $this->redirectToTab('notifications', "Test SMS failed: {$reason}", 'error');
    }

    public function updateLegal(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $data = $request->validate([
            'terms_and_conditions' => ['required', 'string', 'max:50000'],
        ]);

        SystemSetting::setValue('legal', 'terms_and_conditions', $data['terms_and_conditions']);

        return $this->redirectToTab('legal', 'Legal settings saved.');
    }

    public function updateSystem(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $request->validate([
            'maintenance_mode' => ['nullable', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:500'],
        ]);

        SystemSetting::setValue('system', 'maintenance_mode', $request->boolean('maintenance_mode'));
        SystemSetting::setValue('system', 'maintenance_message', $request->input('maintenance_message', ''));

        return $this->redirectToTab('system', 'System settings saved.');
    }

    public function updateBackup(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'folder_id' => ['nullable', 'string', 'max:128'],
            'keep_count' => ['required', 'integer', 'min:1', 'max:90'],
            'run_at' => ['required', 'regex:/^\d{1,2}:\d{2}(:\d{2})?$/'],
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:255'],
            'google_refresh_token' => ['nullable', 'string', 'max:512'],
            'notify_sms' => ['nullable', 'boolean'],
            'notify_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $runAt = substr($data['run_at'], 0, 5);
        if (strlen($runAt) === 4) {
            $runAt = '0'.$runAt;
        }

        $this->databaseBackupService->saveSettings(
            $request->boolean('enabled'),
            (string) ($data['folder_id'] ?? ''),
            (int) $data['keep_count'],
            $runAt,
            $data['google_client_id'] ?? null,
            $request->filled('google_client_secret') ? $data['google_client_secret'] : null,
            $request->boolean('notify_sms'),
            (string) ($data['notify_phone'] ?? ''),
            $request->filled('google_refresh_token') ? $data['google_refresh_token'] : null,
        );

        return $this->redirectToTab('backup', 'Backup settings saved.');
    }

    public function runBackup(): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        set_time_limit(600);

        $result = $this->databaseBackupService->run();

        if ($result['ok']) {
            return $this->redirectToTab('backup', 'Database backup uploaded to Google Drive: '.$result['log']->filename);
        }

        return $this->redirectToTab('backup', $result['log']->message ?: 'Database backup failed.', 'error');
    }

    public function connectGoogleDrive(): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        try {
            return redirect()->away($this->databaseBackupService->connectUrl());
        } catch (\RuntimeException $e) {
            return $this->redirectToTab('backup', $e->getMessage(), 'error');
        }
    }

    public function googleDriveCallback(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        if ($request->filled('error')) {
            return $this->redirectToTab('backup', 'Google connection was cancelled.', 'error');
        }

        $data = $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        try {
            $this->databaseBackupService->completeConnection($data['code'], $data['state']);
        } catch (\RuntimeException $e) {
            return $this->redirectToTab('backup', $e->getMessage(), 'error');
        }

        return $this->redirectToTab('backup', 'Google Drive connected. You can run a backup now.');
    }

    public function disconnectGoogleDrive(): RedirectResponse
    {
        $this->authorize('viewAny', SystemSetting::class);

        $this->databaseBackupService->disconnect();

        return $this->redirectToTab('backup', 'Google Drive disconnected.');
    }

    public function storePackage(StorePackageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $slug = $data['slug'] ?? Str::slug($data['name']);

        $package = SubscriptionPackage::create([
            ...$data,
            'slug' => $this->uniqueSlug($slug),
            'currency' => SystemSetting::platformCurrency(),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? (SubscriptionPackage::max('sort_order') + 1),
        ]);

        $this->syncPackageFeatures($package, Feature::pluck('id')->mapWithKeys(fn ($id) => [$id => false])->all());

        $this->auditLogService->log('package.created', $package, null, $package->toArray());

        return $this->redirectToTab('packages', 'Package created successfully.');
    }

    public function updatePackage(UpdatePackageRequest $request, SubscriptionPackage $package): RedirectResponse
    {
        $this->authorize('update', $package);

        $data = $request->validated();
        $old = $package->toArray();

        if (! empty($data['slug']) && $data['slug'] !== $package->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $package->id);
        } else {
            unset($data['slug']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['currency'] = SystemSetting::platformCurrency();

        $features = $data['features'] ?? null;
        unset($data['features']);

        $package->update($data);

        if (is_array($features)) {
            $this->syncPackageFeatures($package, $features);
            $package->load('features');
            $this->packageFeatureService->applyToSubscribedChurches($package);
        }

        $this->auditLogService->log('package.updated', $package, $old, $package->fresh()->toArray());

        return $this->redirectToTab('packages', "Package \"{$package->name}\" updated.");
    }

    public function destroyPackage(SubscriptionPackage $package): RedirectResponse
    {
        $this->authorize('delete', $package);

        if ($package->subscriptions()->whereIn('status', ['trial', 'active'])->exists()) {
            return $this->redirectToTab('packages', 'Cannot delete a package with active subscriptions.', 'error');
        }

        $this->auditLogService->log('package.deleted', $package, $package->toArray(), null);
        $package->delete();

        return $this->redirectToTab('packages', 'Package deleted.');
    }

    private function allSettings(): array
    {
        return [
            'app_name' => SystemSetting::getValue('general', 'app_name', config('app.name')),
            'support_email' => SystemSetting::getValue('general', 'support_email', 'support@wauminilink.com'),
            'support_phone' => SystemSetting::getValue('general', 'support_phone', ''),
            'email_from_name' => SystemSetting::getValue('general', 'email_from_name', config('app.name')),
            'email_from_address' => SystemSetting::getValue('general', 'email_from_address', config('mail.from.address')),

            'currency' => SystemSetting::platformCurrency(),
            'trial_days' => SystemSetting::getValue('billing', 'trial_days', 14),
            'tax_rate' => SystemSetting::getValue('billing', 'tax_rate', 0),
            'grace_period_days' => SystemSetting::getValue('billing', 'grace_period_days', 3),
            ...SystemSetting::invoiceSettings(),

            'allow_registration' => SystemSetting::getValue('churches', 'allow_registration', true),
            'require_approval' => SystemSetting::getValue('churches', 'require_approval', false),
            'default_timezone' => SystemSetting::getValue('churches', 'default_timezone', 'UTC'),
            'default_country' => SystemSetting::getValue('churches', 'default_country', ''),
            'default_currency' => SystemSetting::defaultChurchCurrency(),

            'sms_enabled' => SystemSetting::getValue('notifications', 'sms_enabled', false),
            'email_notifications' => SystemSetting::getValue('notifications', 'email_notifications', true),
            'expiry_reminder_days' => SystemSetting::getValue('notifications', 'expiry_reminder_days', '7,3,1'),

            'sms_api_url' => SystemSetting::smsGatewayConfig()['api_url'],
            'sms_username' => SystemSetting::smsGatewayConfig()['username'],
            'sms_sender_id' => SystemSetting::smsGatewayConfig()['sender_id'],
            'sms_configured' => SystemSetting::smsGatewayConfigured(),

            'terms_and_conditions' => (string) SystemSetting::getValue(
                'legal',
                'terms_and_conditions',
                config('legal.terms_and_conditions'),
            ),

            'maintenance_mode' => SystemSetting::getValue('system', 'maintenance_mode', false),
            'maintenance_message' => SystemSetting::getValue('system', 'maintenance_message', ''),
        ];
    }

    private function syncPackageFeatures(SubscriptionPackage $package, array $features): void
    {
        $sync = [];
        foreach (Feature::all() as $feature) {
            $sync[$feature->id] = [
                'is_enabled' => (bool) ($features[$feature->id] ?? $features[$feature->key] ?? false),
                'limits' => null,
            ];
        }
        $package->features()->sync($sync);
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $original = $slug;
        $counter = 1;

        while (
            SubscriptionPackage::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function redirectToTab(string $tab, string $message, string $type = 'success'): RedirectResponse
    {
        return redirect()
            ->route('owner.settings.index', ['tab' => $tab])
            ->with($type, $message);
    }
}
