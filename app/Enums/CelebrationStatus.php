<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum CelebrationStatus: string
{
    use HasTranslatableLabel;

    case Upcoming = 'upcoming';
    case Celebrated = 'celebrated';
    case Cancelled = 'cancelled';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Upcoming => 'info',
            self::Celebrated => 'success',
            self::Cancelled => 'secondary',
        };
    }
}
