<?php

namespace App\Traits;

use App\Enums\FinancialApprovalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasFinancialApproval
{
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('approval_status', FinancialApprovalStatus::Pending);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approval_status', FinancialApprovalStatus::Approved);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('approval_status', FinancialApprovalStatus::Rejected);
    }

    public function isPendingApproval(): bool
    {
        return $this->approval_status === FinancialApprovalStatus::Pending;
    }

    public function approve(User $approver, ?string $notes = null): void
    {
        $this->update([
            'approval_status' => FinancialApprovalStatus::Approved,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
            'rejection_reason' => null,
        ]);
    }

    public function reject(User $approver, string $reason): void
    {
        $this->update([
            'approval_status' => FinancialApprovalStatus::Rejected,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
