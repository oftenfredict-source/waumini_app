<?php

namespace App\Models;

use App\Enums\FinancePaymentMethod;
use App\Enums\FinancialApprovalStatus;
use App\Traits\BelongsToChurch;
use App\Traits\HasFinancialApproval;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tithe extends Model
{
    use BelongsToChurch, HasFinancialApproval, HasUuid;

    protected $fillable = [
        'church_id',
        'branch_id',
        'member_id',
        'amount',
        'tithe_date',
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
            'tithe_date' => 'date',
            'payment_method' => FinancePaymentMethod::class,
            'approval_status' => FinancialApprovalStatus::class,
            'approved_at' => 'datetime',
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
}
