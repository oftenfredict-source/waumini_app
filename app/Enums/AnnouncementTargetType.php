<?php

namespace App\Enums;

enum AnnouncementTargetType: string
{
    case All = 'all';
    case Specific = 'specific';
    case Department = 'department';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All Members',
            self::Specific => 'Specific Members',
            self::Department => 'Department',
        };
    }
}
