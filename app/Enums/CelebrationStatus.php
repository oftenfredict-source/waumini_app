<?php

namespace App\Enums;

enum CelebrationStatus: string
{
    case Upcoming = 'upcoming';
    case Celebrated = 'celebrated';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Upcoming',
            self::Celebrated => 'Celebrated',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Upcoming => 'info',
            self::Celebrated => 'success',
            self::Cancelled => 'secondary',
        };
    }
}
