<?php

namespace App\Enums;

enum PledgeType: string
{
    case Building = 'building';
    case Mission = 'mission';
    case Special = 'special';
    case General = 'general';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Building => 'Building Fund',
            self::Mission => 'Mission',
            self::Special => 'Special Project',
            self::General => 'General',
            self::Other => 'Other',
        };
    }
}
