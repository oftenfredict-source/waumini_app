<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum CelebrationType: string
{
    use HasTranslatableLabel;

    case Birthday = 'birthday';
    case WeddingAnniversary = 'wedding_anniversary';
    case Other = 'other';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Birthday => 'info',
            self::WeddingAnniversary => 'primary',
            self::Other => 'secondary',
        };
    }
}
