<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum ExpenseCategory: string
{
    use HasTranslatableLabel;

    case Utilities = 'utilities';
    case Maintenance = 'maintenance';
    case Salaries = 'salaries';
    case Supplies = 'supplies';
    case Missions = 'missions';
    case Events = 'events';
    case Transport = 'transport';
    case Other = 'other';

}
