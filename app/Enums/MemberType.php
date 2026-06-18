<?php

namespace App\Enums;

enum MemberType: string
{
    case Father = 'father';
    case Mother = 'mother';
    case Independent = 'independent';

    public function label(): string
    {
        return match ($this) {
            self::Father => 'Father',
            self::Mother => 'Mother',
            self::Independent => 'Independent',
        };
    }
}
