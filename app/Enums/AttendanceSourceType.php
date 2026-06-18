<?php

namespace App\Enums;

enum AttendanceSourceType: string
{
    case ChurchService = 'church_service';
    case SpecialEvent = 'special_event';

    public function label(): string
    {
        return match ($this) {
            self::ChurchService => 'Church Service',
            self::SpecialEvent => 'Special Event',
        };
    }
}
