<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum SpecialEventStatus: string
{
    use HasTranslatableLabel;

    case Scheduled = 'scheduled';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Scheduled => 'info',
            self::Ongoing => 'warning',
            self::Completed => 'success',
            self::Cancelled => 'danger',
        };
    }
}
