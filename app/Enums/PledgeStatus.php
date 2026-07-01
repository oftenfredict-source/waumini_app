<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum PledgeStatus: string
{
    use HasTranslatableLabel;

    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Overdue = 'overdue';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'primary',
            self::Completed => 'success',
            self::Cancelled => 'secondary',
            self::Overdue => 'danger',
        };
    }
}
