<?php

namespace App\Enums;

enum AnnouncementType: string
{
    case General = 'general';
    case Urgent = 'urgent';
    case Event = 'event';
    case Reminder = 'reminder';

    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Urgent => 'Urgent',
            self::Event => 'Event',
            self::Reminder => 'Reminder',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::General => 'info',
            self::Urgent => 'danger',
            self::Event => 'primary',
            self::Reminder => 'warning',
        };
    }
}
