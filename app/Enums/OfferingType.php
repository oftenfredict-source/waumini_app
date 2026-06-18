<?php

namespace App\Enums;

enum OfferingType: string
{
    case General = 'general';
    case Special = 'special';
    case Thanksgiving = 'thanksgiving';
    case BuildingFund = 'building_fund';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Special => 'Special',
            self::Thanksgiving => 'Thanksgiving',
            self::BuildingFund => 'Building Fund',
            self::Other => 'Other',
        };
    }
}
