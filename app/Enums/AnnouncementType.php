<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum AnnouncementType: string
{
    use HasTranslatableLabel;

    case General = 'general';
    case Urgent = 'urgent';
    case Event = 'event';
    case Reminder = 'reminder';

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
