<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum AssetStatus: string
{
    use HasTranslatableLabel;

    case Active = 'active';
    case UnderMaintenance = 'under_maintenance';
    case Disposed = 'disposed';
    case Lost = 'lost';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::UnderMaintenance => 'warning',
            self::Disposed => 'secondary',
            self::Lost => 'danger',
        };
    }
}
