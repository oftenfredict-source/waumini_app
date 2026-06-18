<?php

namespace App\Models;

use App\Enums\LeadershipPosition;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leader extends Model
{
    use BelongsToChurch, HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'branch_id',
        'member_id',
        'position',
        'position_title',
        'description',
        'appointment_date',
        'end_date',
        'is_active',
        'appointed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'position' => LeadershipPosition::class,
            'appointment_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(ChurchBranch::class, 'branch_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function positionLabel(): string
    {
        if ($this->position === LeadershipPosition::Other && $this->position_title) {
            return $this->position_title;
        }

        return $this->position->label();
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }
}
