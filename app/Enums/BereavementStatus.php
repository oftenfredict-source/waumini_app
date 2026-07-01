<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum BereavementStatus: string
{
    use HasTranslatableLabel;

    case Open = 'open';
    case Closed = 'closed';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'success',
            self::Closed => 'secondary',
        };
    }
}
