<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum BudgetStatus: string
{
    use HasTranslatableLabel;

    case Active = 'active';
    case Draft = 'draft';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'primary',
            self::Draft => 'secondary',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }
}
