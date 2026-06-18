<?php

namespace App\Models;

use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChurchBranch extends Model
{
    use BelongsToChurch, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'name',
        'code',
        'is_headquarters',
        'address',
        'city',
        'phone',
        'email',
        'pastor_name',
        'logo_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_headquarters' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'branch_id');
    }

    public function leaders(): HasMany
    {
        return $this->hasMany(Leader::class, 'branch_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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

        $path = \Illuminate\Support\Facades\Storage::disk('public')->path($this->logo_path);

        return is_file($path) ? $path : null;
    }

    public function displayLabel(): string
    {
        return $this->is_headquarters
            ? $this->name.' (Headquarters)'
            : $this->name;
    }
}
