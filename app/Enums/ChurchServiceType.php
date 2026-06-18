<?php

namespace App\Enums;

enum ChurchServiceType: string
{
    case Sunday = 'sunday';
    case SundaySchool = 'sunday_school';
    case MidWeek = 'mid_week';
    case Prayer = 'prayer';
    case Extra = 'extra';

    public function label(): string
    {
        return match ($this) {
            self::Sunday => 'Sunday Service',
            self::SundaySchool => 'Sunday School',
            self::MidWeek => 'Mid-week Service',
            self::Prayer => 'Prayer Service',
            self::Extra => 'Extra Service',
        };
    }

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
