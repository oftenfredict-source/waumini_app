<?php

namespace App\Models;

use App\Enums\FinancePaymentMethod;
use App\Enums\FinancialApprovalStatus;
use App\Traits\HasFinancialApproval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PledgePayment extends Model
{
    use HasFinancialApproval;

    protected $fillable = [
        'pledge_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'recorded_by',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
            'payment_method' => FinancePaymentMethod::class,
            'approval_status' => FinancialApprovalStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function pledge(): BelongsTo
    {
        return $this->belongsTo(Pledge::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeForChurch(Builder $query, int $churchId): Builder
    {
        return $query->whereHas('pledge', fn ($q) => $q->where('church_id', $churchId));
    }
}
