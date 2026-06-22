<?php

namespace App\Enums;

enum AssetStatus: string
{
    case Active = 'active';
    case UnderMaintenance = 'under_maintenance';
    case Disposed = 'disposed';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::UnderMaintenance => 'Under Maintenance',
            self::Disposed => 'Disposed',
            self::Lost => 'Lost',
        };
    }

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
