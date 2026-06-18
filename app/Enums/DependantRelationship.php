<?php

namespace App\Enums;

enum DependantRelationship: string
{
    case Child = 'child';
    case Relative = 'relative';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Child => 'Child',
            self::Relative => 'Relative',
            self::Other => 'Other',
        };
    }
}
