<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum PromiseGuestStatus: string
{
    use HasTranslatableLabel;

    case Pending = 'pending';
    case Notified = 'notified';
    case Attended = 'attended';
    case Cancelled = 'cancelled';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Notified => 'info',
            self::Attended => 'success',
            self::Cancelled => 'secondary',
        };
    }
}
