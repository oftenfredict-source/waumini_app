<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum ExpenseStatus: string
{
    use HasTranslatableLabel;

    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';
    case Rejected = 'rejected';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'info',
            self::Paid => 'success',
            self::Rejected => 'danger',
        };
    }
}
