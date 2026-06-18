<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'module',
    ];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPackage::class, 'package_features', 'feature_id', 'package_id')
            ->withPivot(['is_enabled', 'limits'])
            ->withTimestamps();
    }
}
