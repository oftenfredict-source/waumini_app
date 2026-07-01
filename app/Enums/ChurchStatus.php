<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum ChurchStatus: string
{
    use HasTranslatableLabel;

    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
    case Expired = 'expired';
    case Trial = 'trial';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Active => 'success',
            self::Suspended => 'danger',
            self::Expired => 'secondary',
            self::Trial => 'info',
        };
    }
}
