<?php

namespace App\Enums;

enum WeddingType: string
{
    case Church = 'church';
    case Civil = 'civil';
    case Traditional = 'traditional';
    case Customary = 'customary';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Church => 'Church Wedding',
            self::Civil => 'Civil Wedding',
            self::Traditional => 'Traditional Wedding',
            self::Customary => 'Customary Wedding',
            self::Other => 'Other',
        };
    }
}
