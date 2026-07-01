<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum SpecialEventCategory: string
{
    use HasTranslatableLabel;

    case Conference = 'conference';
    case Crusade = 'crusade';
    case Wedding = 'wedding';
    case Baptism = 'baptism';
    case Ordination = 'ordination';
    case Thanksgiving = 'thanksgiving';
    case Youth = 'youth';
    case Revival = 'revival';
    case Seminar = 'seminar';
    case Other = 'other';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Conference => 'primary',
            self::Crusade => 'info',
            self::Wedding => 'danger',
            self::Baptism => 'success',
            self::Ordination => 'warning',
            self::Thanksgiving => 'secondary',
            self::Youth => 'info',
            self::Revival => 'primary',
            self::Seminar => 'dark',
            self::Other => 'secondary',
        };
    }
}
