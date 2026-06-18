<?php

namespace App\Enums;

enum BereavementStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'success',
            self::Closed => 'secondary',
        };
    }
}
