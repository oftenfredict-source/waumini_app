<?php

namespace App\Enums;

enum SpecialEventCategory: string
{
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

    public function label(): string
    {
        return match ($this) {
            self::Conference => 'Conference',
            self::Crusade => 'Crusade / Evangelism',
            self::Wedding => 'Wedding',
            self::Baptism => 'Baptism',
            self::Ordination => 'Ordination',
            self::Thanksgiving => 'Harvest / Thanksgiving',
            self::Youth => 'Youth Event',
            self::Revival => 'Revival',
            self::Seminar => 'Seminar / Workshop',
            self::Other => 'Other',
        };
    }

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
