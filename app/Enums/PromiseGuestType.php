<?php

namespace App\Enums;

enum PromiseGuestType: string
{
    case Promised = 'promised';
    case Temporary = 'temporary';

    public function label(): string
    {
        return match ($this) {
            self::Promised => 'Promised Guest',
            self::Temporary => 'Temporary Guest',
        };
    }
}
