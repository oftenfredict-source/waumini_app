<?php

namespace App\Models;

use App\Enums\FinancePaymentMethod;
use App\Enums\FinancialApprovalStatus;
use App\Enums\OfferingType;
use App\Traits\BelongsToChurch;
use App\Traits\HasFinancialApproval;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offering extends Model
{
    use BelongsToChurch, HasFinancialApproval, HasUuid;

    protected $fillable = [
        'church_id',
        'branch_id',
        'member_id',
        'church_service_id',
        'amount',
        'offering_date',
        'offering_type',
        'offering_type_other',
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
            'offering_date' => 'date',
            'offering_type' => OfferingType::class,
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

    public function churchService(): BelongsTo
    {
        return $this->belongsTo(ChurchService::class);
    }

    public function isGeneralOffering(): bool
    {
        return $this->member_id === null;
    }

    public function contributorLabel(): string
    {
        if ($this->member) {
            return $this->member->full_name;
        }

        if ($this->churchService) {
            return 'General — '.$this->churchService->displayTitle();
        }

        return 'General Offering';
    }

    public function offeringTypeLabel(): string
    {
        if ($this->offering_type === OfferingType::Other && $this->offering_type_other) {
            return $this->offering_type_other;
        }

        return $this->offering_type?->label() ?? '—';
    }
}
