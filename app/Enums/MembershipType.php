<?php

namespace App\Enums;

enum MembershipType: string
{
    case Permanent = 'permanent';
    case Temporary = 'temporary';

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Permanent',
            self::Temporary => 'Temporary',
        };
    }
}
