<?php

namespace App\Enums;

enum BudgetType: string
{
    case Annual = 'annual';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Annual => 'Annual',
            self::Other => 'Other',
        };
    }
}
