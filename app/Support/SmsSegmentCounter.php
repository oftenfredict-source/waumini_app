<?php

namespace App\Support;

class SmsSegmentCounter
{
    public const SEGMENT_LENGTH = 160;

    public static function count(?string $message): int
    {
        $length = mb_strlen(trim((string) $message));

        if ($length === 0) {
            return 1;
        }

        return (int) ceil($length / self::SEGMENT_LENGTH);
    }
}
