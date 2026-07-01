<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum FinancialApprovalStatus: string
{
    use HasTranslatableLabel;

    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
