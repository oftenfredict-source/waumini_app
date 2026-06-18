<?php

namespace App\Enums;

enum CelebrationSource: string
{
    case Auto = 'auto';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Auto => 'From Member Profile',
            self::Manual => 'Manual Entry',
        };
    }
}
