<?php

namespace App\Enums;

enum OfferingContributionType: string
{
    case Member = 'member';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Member => 'Individual Member',
            self::General => 'General Offering',
        };
    }
}
