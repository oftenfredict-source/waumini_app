<?php

namespace App\Models;

use App\Enums\ChurchStatus;
use App\Support\TenantDomain;
use App\Support\WauminiBrand;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Church extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'denomination',
        'pastor_name',
        'logo_path',
        'seal_path',
        'pastor_signature_path',
        'status',
        'trial_ends_at',
        'suspended_at',
        'suspended_reason',
        'timezone',
        'locale',
        'currency',
        'branches_enabled',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'status' => ChurchStatus::class,
            'trial_ends_at' => 'datetime',
            'suspended_at' => 'datetime',
            'branches_enabled' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function domains(): HasMany
    {
        return $this->hasMany(ChurchDomain::class);
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(ChurchDomain::class)->where('is_primary', true);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ChurchSubscription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function hasPackageFeature(string $key): bool
    {
        $package = $this->activeSubscription?->package;

        if (! $package) {
            return false;
        }

        return $package->hasFeature($key);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(ChurchSubscription::class)
            ->whereIn('status', ['trial', 'active'])
            ->latestOfMany();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function adminUser(): HasOne
    {
        return $this->hasOne(User::class)
            ->where('user_type', 'church_admin');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(ChurchBranch::class);
    }

    public function headquarters(): HasOne
    {
        return $this->hasOne(ChurchBranch::class)->where('is_headquarters', true);
    }

    public function tenantDomain(): string
    {
        return TenantDomain::forChurch($this);
    }

    public function subdomainUrl(string $path = '/'): string
    {
        return TenantDomain::churchUrl($this, $path);
    }

    public function portalUrl(string $path = '/'): string
    {
        if (config('waumini.use_subdomain_urls')) {
            return $this->subdomainUrl($path);
        }

        return TenantDomain::appUrl($path, ['church' => $this->slug]);
    }

    public function branchesEnabled(): bool
    {
        return (bool) $this->branches_enabled;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [ChurchStatus::Active, ChurchStatus::Trial], true);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path
            ? asset('storage/'.$this->logo_path)
            : null;
    }

    public function logoAbsolutePath(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        $path = Storage::disk('public')->path($this->logo_path);

        return is_file($path) ? $path : null;
    }

    public function sealUrl(): ?string
    {
        return $this->seal_path
            ? asset('storage/'.$this->seal_path)
            : null;
    }

    public function sealAbsolutePath(): ?string
    {
        if (! $this->seal_path) {
            return null;
        }

        $path = Storage::disk('public')->path($this->seal_path);

        return is_file($path) ? $path : null;
    }

    public function pastorSignatureUrl(): ?string
    {
        return $this->pastor_signature_path
            ? asset('storage/'.$this->pastor_signature_path)
            : null;
    }

    public function pastorSignatureAbsolutePath(): ?string
    {
        if (! $this->pastor_signature_path) {
            return null;
        }

        $path = Storage::disk('public')->path($this->pastor_signature_path);

        return is_file($path) ? $path : null;
    }

    public function hasOfficialSeal(): bool
    {
        return (bool) $this->sealAbsolutePath();
    }

    public function hasPastorSignature(): bool
    {
        return (bool) $this->pastorSignatureAbsolutePath();
    }

    /**
     * @return array<string, string>
     */
    public function certificateTemplates(): array
    {
        $stored = is_array($this->settings) ? ($this->settings['certificate_templates'] ?? []) : [];

        if (! is_array($stored)) {
            return [];
        }

        $templates = [];
        foreach ($stored as $type => $path) {
            if (is_string($type) && is_string($path) && $path !== '') {
                $templates[$type] = $path;
            }
        }

        return $templates;
    }

    public function certificateTemplatePath(string $type): ?string
    {
        return $this->certificateTemplates()[$type] ?? null;
    }

    public function certificateTemplateUrl(string $type): ?string
    {
        $path = $this->certificateTemplatePath($type);

        return $path ? asset('storage/'.$path) : null;
    }

    public function certificateTemplateAbsolutePath(string $type): ?string
    {
        $path = $this->certificateTemplatePath($type);

        if (! $path) {
            return null;
        }

        $absolute = Storage::disk('public')->path($path);

        return is_file($absolute) ? $absolute : null;
    }

    public function hasCertificateTemplate(string $type): bool
    {
        return (bool) $this->certificateTemplateAbsolutePath($type);
    }

    public function brandColor(): string
    {
        $stored = is_array($this->settings) ? ($this->settings['brand_color'] ?? null) : null;

        return WauminiBrand::normalizeHex(
            is_string($stored) ? $stored : null,
            (string) config('church_settings.defaults.brand_color', WauminiBrand::defaultColor())
        );
    }

    /**
     * @return array{
     *     brand: string,
     *     hover: string,
     *     active: string,
     *     darkest: string,
     *     logo: string,
     *     soft: string,
     *     rgb: string
     * }
     */
    public function brandPalette(): array
    {
        return WauminiBrand::palette($this->brandColor());
    }
}
