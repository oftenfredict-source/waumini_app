<?php

namespace App\Models;

use App\Enums\PledgePaymentFrequency;
use App\Enums\PledgeStatus;
use App\Enums\PledgeType;
use App\Traits\BelongsToChurch;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pledge extends Model
{
    use BelongsToChurch, HasUuid;

    protected $fillable = [
        'church_id',
        'member_id',
        'pledge_amount',
        'amount_paid',
        'pledge_date',
        'due_date',
        'pledge_type',
        'pledge_type_other',
        'payment_frequency',
        'purpose',
        'notes',
        'status',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'pledge_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'pledge_date' => 'date',
            'due_date' => 'date',
            'pledge_type' => PledgeType::class,
            'payment_frequency' => PledgePaymentFrequency::class,
            'status' => PledgeStatus::class,
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PledgePayment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', PledgeStatus::Active);
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->pledge_amount - (float) $this->amount_paid);
    }

    public function progressPercentage(): float
    {
        if ((float) $this->pledge_amount <= 0) {
            return 0.0;
        }

        return round(((float) $this->amount_paid / (float) $this->pledge_amount) * 100, 1);
    }

    public function isCompleted(): bool
    {
        return (float) $this->amount_paid >= (float) $this->pledge_amount;
    }

    public function pledgeTypeLabel(): string
    {
        if ($this->pledge_type === PledgeType::Other && $this->pledge_type_other) {
            return $this->pledge_type_other;
        }

        return $this->pledge_type?->label() ?? '—';
    }

    public function refreshStatus(): void
    {
        if ($this->status === PledgeStatus::Cancelled) {
            return;
        }

        if ($this->isCompleted()) {
            $this->update(['status' => PledgeStatus::Completed]);

            return;
        }

        if ($this->due_date && $this->due_date->isPast() && ! $this->isCompleted()) {
            $this->update(['status' => PledgeStatus::Overdue]);

            return;
        }

        if ($this->status !== PledgeStatus::Active) {
            $this->update(['status' => PledgeStatus::Active]);
        }
    }
}
