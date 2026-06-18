<?php

namespace App\Models;

use App\Enums\BereavementStatus;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BereavementEvent extends Model
{
    use BelongsToChurch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'deceased_name',
        'affected_member_id',
        'family_details',
        'related_departments',
        'incident_date',
        'contribution_start_date',
        'contribution_end_date',
        'status',
        'notes',
        'fund_usage',
        'created_by',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'incident_date' => 'date',
            'contribution_start_date' => 'date',
            'contribution_end_date' => 'date',
            'status' => BereavementStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function affectedMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'affected_member_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(BereavementContribution::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', BereavementStatus::Open);
    }

    public function isOpen(): bool
    {
        return $this->status === BereavementStatus::Open;
    }

    public function isExpired(): bool
    {
        return $this->isOpen() && $this->contribution_end_date->isPast();
    }

    public function daysRemaining(): int
    {
        if (! $this->isOpen()) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->contribution_end_date->startOfDay(), false));
    }

    public function totalContributions(): float
    {
        return (float) $this->contributions()->where('has_contributed', true)->sum('amount');
    }

    public function contributorsCount(): int
    {
        return $this->contributions()->where('has_contributed', true)->count();
    }

    public function pendingCount(): int
    {
        return $this->contributions()->where('has_contributed', false)->count();
    }

    public function close(?string $fundUsage = null): void
    {
        $this->update([
            'status' => BereavementStatus::Closed,
            'closed_at' => now(),
            'fund_usage' => $fundUsage ?? $this->fund_usage,
        ]);
    }
}
