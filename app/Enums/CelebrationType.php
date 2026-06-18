<?php

namespace App\Enums;

enum CelebrationType: string
{
    case Birthday = 'birthday';
    case WeddingAnniversary = 'wedding_anniversary';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Birthday => 'Birthday',
            self::WeddingAnniversary => 'Wedding Anniversary',
            self::Other => 'Other Celebration',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Birthday => 'info',
            self::WeddingAnniversary => 'primary',
            self::Other => 'secondary',
        };
    }
}
