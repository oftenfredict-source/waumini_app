<?php

namespace App\Models;

use App\Enums\BudgetStatus;
use App\Enums\BudgetType;
use App\Enums\ExpenseStatus;
use App\Enums\FinancialApprovalStatus;
use App\Traits\BelongsToChurch;
use App\Traits\HasFinancialApproval;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use BelongsToChurch, HasFinancialApproval, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'budget_name',
        'budget_type',
        'purpose',
        'primary_offering_type',
        'requires_approval',
        'fiscal_year',
        'start_date',
        'end_date',
        'total_budget',
        'allocated_amount',
        'spent_amount',
        'description',
        'status',
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
            'budget_type' => BudgetType::class,
            'status' => BudgetStatus::class,
            'approval_status' => FinancialApprovalStatus::class,
            'total_budget' => 'decimal:2',
            'allocated_amount' => 'decimal:2',
            'spent_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'requires_approval' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(BudgetLineItem::class)->orderBy('sort_order');
    }

    public function offeringAllocations(): HasMany
    {
        return $this->hasMany(BudgetOfferingAllocation::class);
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->total_budget - (float) $this->spent_amount);
    }

    public function pendingExpensesAmount(): float
    {
        return (float) $this->expenses()
            ->where('status', '!=', ExpenseStatus::Paid->value)
            ->whereIn('approval_status', [
                FinancialApprovalStatus::Pending,
                FinancialApprovalStatus::Approved,
            ])
            ->sum('amount');
    }

    public function totalCommitted(): float
    {
        return (float) $this->spent_amount + $this->pendingExpensesAmount();
    }

    public function utilizationPercentage(): float
    {
        if ((float) $this->total_budget <= 0) {
            return 0.0;
        }

        return round(((float) $this->spent_amount / (float) $this->total_budget) * 100, 1);
    }

    public function committedUtilizationPercentage(): float
    {
        if ((float) $this->total_budget <= 0) {
            return 0.0;
        }

        return round(($this->totalCommitted() / (float) $this->total_budget) * 100, 1);
    }

    public function isFullyFunded(): bool
    {
        return (float) $this->offeringAllocations()->sum('allocated_amount') >= (float) $this->total_budget;
    }

    public function fundingPercentage(): float
    {
        if ((float) $this->total_budget <= 0) {
            return 0.0;
        }

        return round(((float) $this->offeringAllocations()->sum('allocated_amount') / (float) $this->total_budget) * 100, 1);
    }
}
