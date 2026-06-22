<?php

namespace App\Support;

class Currency
{
    public static function label(?string $code): string
    {
        return match (strtoupper((string) $code)) {
            'TZS', 'TSH' => 'Tsh',
            default => strtoupper((string) $code),
        };
    }

    public static function decimals(?string $code): int
    {
        return in_array(strtoupper((string) $code), ['TZS', 'UGX', 'KES', 'RWF', 'BIF'], true) ? 0 : 2;
    }
}
