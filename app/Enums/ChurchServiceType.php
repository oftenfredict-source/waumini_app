<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum ChurchServiceType: string
{
    use HasTranslatableLabel;

    case Sunday = 'sunday';
    case SundaySchool = 'sunday_school';
    case MidWeek = 'mid_week';
    case Prayer = 'prayer';
    case Extra = 'extra';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Sunday => 'primary',
            self::SundaySchool => 'success',
            self::MidWeek => 'info',
            self::Prayer => 'warning',
            self::Extra => 'secondary',
        };
    }

    public function isSundaySchool(): bool
    {
        return $this === self::SundaySchool;
    }
}
