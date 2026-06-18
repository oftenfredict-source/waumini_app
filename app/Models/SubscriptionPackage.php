<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPackage extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'installation_price',
        'yearly_price',
        'currency',
        'trial_days',
        'max_members',
        'max_sms_monthly',
        'max_storage_mb',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'installation_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'package_features', 'package_id', 'feature_id')
            ->withPivot(['is_enabled', 'limits'])
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ChurchSubscription::class, 'package_id');
    }

    public function hasFeature(string $key): bool
    {
        if (! $this->relationLoaded('features')) {
            $this->load('features');
        }

        return $this->features
            ->firstWhere('key', $key)
            ?->pivot
            ?->is_enabled ?? false;
    }

    public function monthlyRecurringRevenue(): float
    {
        return (float) $this->yearly_price / 12;
    }
}
