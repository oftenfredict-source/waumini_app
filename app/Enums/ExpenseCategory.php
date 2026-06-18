<?php

namespace App\Enums;

enum ExpenseCategory: string
{
    case Utilities = 'utilities';
    case Maintenance = 'maintenance';
    case Salaries = 'salaries';
    case Supplies = 'supplies';
    case Missions = 'missions';
    case Events = 'events';
    case Transport = 'transport';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Utilities => 'Utilities',
            self::Maintenance => 'Maintenance',
            self::Salaries => 'Salaries & Allowances',
            self::Supplies => 'Supplies',
            self::Missions => 'Missions',
            self::Events => 'Events',
            self::Transport => 'Transport',
            self::Other => 'Other',
        };
    }
}
