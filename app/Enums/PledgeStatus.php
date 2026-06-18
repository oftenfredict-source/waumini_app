<?php

namespace App\Enums;

enum PledgeStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Overdue = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
            self::Overdue => 'Overdue',
        };
    }

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
