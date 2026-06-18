<?php

namespace App\Enums;

enum PromiseGuestStatus: string
{
    case Pending = 'pending';
    case Notified = 'notified';
    case Attended = 'attended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Notified => 'Notified',
            self::Attended => 'Attended',
            self::Cancelled => 'Cancelled',
        };
    }

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
