<?php

namespace App\Enums;

enum AssetCategory: string
{
    case Building = 'building';
    case Vehicle = 'vehicle';
    case Furniture = 'furniture';
    case Electronics = 'electronics';
    case Musical = 'musical';
    case Equipment = 'equipment';
    case Land = 'land';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Building => 'Building / Property',
            self::Vehicle => 'Vehicle',
            self::Furniture => 'Furniture',
            self::Electronics => 'Electronics',
            self::Musical => 'Musical Instruments',
            self::Equipment => 'Equipment',
            self::Land => 'Land',
            self::Other => 'Other',
        };
    }
}
