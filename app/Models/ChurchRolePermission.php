<?php

namespace App\Models;

use App\Traits\BelongsToChurch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChurchRolePermission extends Model
{
    use BelongsToChurch;

    protected $fillable = [
        'church_id',
        'role',
        'permission',
    ];

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }
}
