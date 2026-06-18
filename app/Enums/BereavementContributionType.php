<?php

namespace App\Enums;

enum BereavementContributionType: string
{
    case Individual = 'individual';
    case FamilyWide = 'family_wide';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Individual',
            self::FamilyWide => 'Family-wide',
        };
    }
}
