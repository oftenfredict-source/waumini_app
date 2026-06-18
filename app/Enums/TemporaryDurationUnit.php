<?php

namespace App\Enums;

enum TemporaryDurationUnit: string
{
    case Month = 'month';
    case Year = 'year';

    public function label(): string
    {
        return match ($this) {
            self::Month => 'Month(s)',
            self::Year => 'Year(s)',
        };
    }
}
