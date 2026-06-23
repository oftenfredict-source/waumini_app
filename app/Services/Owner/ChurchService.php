<?php

namespace App\Services\Owner;

use App\Enums\ChurchStaffRole;
use App\Enums\ChurchStatus;
use App\Enums\SubscriptionStatus;
use App\Enums\UserStatus;
use App\Enums\UserType;
use App\Models\Church;
use App\Models\ChurchSubscription;
use App\Models\SubscriptionPackage;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\TenantDomain;
use App\Services\Church\BranchService;
use App\Services\Owner\PackageFeatureService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChurchService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly BranchService $branchService,
        private readonly PackageFeatureService $packageFeatureService,
    ) {}

    /**
     * @return array{church: Church, admin_password: string}
     */
    public function create(array $data, ?SubscriptionPackage $package = null): array
    {
        return DB::transaction(function () use ($data, $package) {
            $slug = $this->generateUniqueSlug($data['slug'] ?? $data['name']);

            $church = Church::create([
                'name' => $data['name'],
                'slug' => $slug,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? null,
                'denomination' => $data['denomination'] ?? null,
                'pastor_name' => $data['pastor_name'] ?? null,
                'status' => ChurchStatus::Trial,
                'trial_ends_at' => now()->addDays($package?->trial_days ?? 14),
                'timezone' => $data['timezone'] ?? SystemSetting::getValue('churches', 'default_timezone', 'UTC'),
                'currency' => $data['currency'] ?? SystemSetting::defaultChurchCurrency(),
                'branches_enabled' => (bool) ($data['branches_enabled'] ?? false),
            ]);

            $church->domains()->create([
                'domain' => TenantDomain::forSlug($slug),
                'type' => 'subdomain',
                'is_primary' => true,
                'ssl_status' => 'pending',
            ]);

            if ($package) {
                $this->assignSubscription($church, $package, $data['billing_cycle'] ?? 'yearly');
                $package->load('features');
                $this->packageFeatureService->applyToChurch($church->fresh(), $package);
            } elseif ($church->branches_enabled) {
                $this->branchService->ensureHeadquartersBranch($church);
            }

            $plainPassword = $this->createAdminUser($church, $data);

            $this->auditLogService->log('church.created', $church, null, $church->toArray(), $church->id);

            return [
                'church' => $church->load(['primaryDomain', 'activeSubscription.package', 'adminUser']),
                'admin_password' => $plainPassword,
            ];
        });
    }

    public function update(Church $church, array $data): Church
    {
        $old = $church->only(array_keys($data));
        $wasBranchesEnabled = $church->branches_enabled;

        if (array_key_exists('branches_enabled', $data)) {
            $data['branches_enabled'] = (bool) $data['branches_enabled'];
        }

        $church->update($data);

        if (array_key_exists('phone', $data)) {
            $this->syncAdminLoginPhone($church);
        }

        if (! $wasBranchesEnabled && $church->branches_enabled) {
            $this->branchService->ensureHeadquartersBranch($church);
        }

        $this->auditLogService->log('church.updated', $church, $old, $church->only(array_keys($data)), $church->id);

        return $church->fresh(['primaryDomain', 'activeSubscription.package']);
    }

    public function suspend(Church $church, ?string $reason = null): Church
    {
        $church->update([
            'status' => ChurchStatus::Suspended,
            'suspended_at' => now(),
            'suspended_reason' => $reason,
        ]);

        $this->auditLogService->log('church.suspended', $church, null, ['reason' => $reason], $church->id);

        return $church;
    }

    public function activate(Church $church): Church
    {
        $church->update([
            'status' => ChurchStatus::Active,
            'suspended_at' => null,
            'suspended_reason' => null,
        ]);

        $this->auditLogService->log('church.activated', $church, null, null, $church->id);

        return $church;
    }

    public function delete(Church $church): void
    {
        $this->auditLogService->log('church.deleted', $church, $church->toArray(), null, $church->id);
        $church->delete();
    }

    public function regenerateAdminPassword(Church $church): string
    {
        $admin = $church->adminUser;

        if (! $admin) {
            throw new \RuntimeException('This church has no admin account.');
        }

        $plainPassword = Str::password(12, symbols: false);
        $admin->update(['password' => $plainPassword]);

        $this->auditLogService->log('church.admin_password_reset', $admin, null, ['email' => $admin->email], $church->id);

        return $plainPassword;
    }

    public function createMissingAdmin(Church $church, ?string $email = null): string
    {
        if ($church->adminUser) {
            throw new \RuntimeException('This church already has an admin account.');
        }

        return $this->createAdminUser($church, [
            'email' => $church->email,
            'admin_email' => $email ?? $church->email,
            'pastor_name' => $church->pastor_name,
            'phone' => $church->phone,
        ]);
    }

    public function assignSubscription(Church $church, SubscriptionPackage $package, string $billingCycle = 'monthly'): ChurchSubscription
    {
        $church->subscriptions()
            ->whereIn('status', [SubscriptionStatus::Trial->value, SubscriptionStatus::Active->value])
            ->update([
                'status' => SubscriptionStatus::Cancelled->value,
                'cancelled_at' => now(),
            ]);

        $trialEndsAt = now()->addDays($package->trial_days);

        return $church->subscriptions()->create([
            'package_id' => $package->id,
            'billing_cycle' => $billingCycle,
            'status' => SubscriptionStatus::Trial,
            'starts_at' => now(),
            'trial_ends_at' => $trialEndsAt,
            'ends_at' => $trialEndsAt,
            'auto_renew' => true,
        ]);
    }

    public function syncPrimaryDomain(Church $church): bool
    {
        $expectedDomain = TenantDomain::forChurch($church);
        $primaryDomain = $church->primaryDomain;

        if ($primaryDomain) {
            if ($primaryDomain->domain === $expectedDomain) {
                return false;
            }

            $primaryDomain->update(['domain' => $expectedDomain]);

            return true;
        }

        $church->domains()->create([
            'domain' => $expectedDomain,
            'type' => 'subdomain',
            'is_primary' => true,
            'ssl_status' => 'pending',
        ]);

        return true;
    }

    private function createAdminUser(Church $church, array $data): string
    {
        $email = $data['admin_email'] ?? $data['email'];
        $name = $data['pastor_name'] ?? $church->name.' Admin';
        $plainPassword = Str::password(12, symbols: false);

        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $data['phone'] ?? $church->phone,
            'password' => $plainPassword,
            'user_type' => UserType::ChurchAdmin,
            'status' => UserStatus::Active,
            'church_id' => $church->id,
            'email_verified_at' => now(),
        ]);

        if (\Spatie\Permission\Models\Role::where('name', ChurchStaffRole::Administrator->value)->exists()) {
            $admin->assignRole(ChurchStaffRole::Administrator->value);
        } elseif (\Spatie\Permission\Models\Role::where('name', 'church_admin')->exists()) {
            $admin->assignRole('church_admin');
        }

        $this->auditLogService->log('church.admin_created', $admin, null, ['email' => $email], $church->id);

        return $plainPassword;
    }

    private function syncAdminLoginPhone(Church $church): void
    {
        $admin = $church->adminUser;

        if (! $admin || ! empty($admin->phone) || empty($church->phone)) {
            return;
        }

        $admin->update(['phone' => $church->phone]);
    }

    private function generateUniqueSlug(string $value): string
    {
        $slug = Str::slug($value);
        $original = $slug;
        $counter = 1;

        while (Church::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
