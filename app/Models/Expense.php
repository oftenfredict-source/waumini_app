<?php

namespace App\Models;

use App\Enums\ExpenseCategory;
use App\Enums\ExpenseStatus;
use App\Enums\FinancePaymentMethod;
use App\Enums\FinancialApprovalStatus;
use App\Traits\BelongsToChurch;
use App\Traits\HasFinancialApproval;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use BelongsToChurch, HasFinancialApproval, HasUuid, SoftDeletes;

    protected $fillable = [
        'church_id',
        'branch_id',
        'budget_id',
        'expense_category',
        'expense_name',
        'amount',
        'expense_date',
        'payment_method',
        'reference_number',
        'description',
        'vendor',
        'receipt_number',
        'status',
        'notes',
        'recorded_by',
        'approval_status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'paid_at',
        'paid_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
            'expense_category' => ExpenseCategory::class,
            'payment_method' => FinancePaymentMethod::class,
            'status' => ExpenseStatus::class,
            'approval_status' => FinancialApprovalStatus::class,
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function church(): BelongsTo
    {
        return $this->belongsTo(Church::class);
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function isPaid(): bool
    {
        return $this->status === ExpenseStatus::Paid;
    }

    public function canBeMarkedPaid(): bool
    {
        return $this->approval_status === FinancialApprovalStatus::Approved
            && ! $this->isPaid();
    }
}
