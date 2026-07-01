<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum OfferingType: string
{
    use HasTranslatableLabel;

    case General = 'general';
    case Special = 'special';
    case Thanksgiving = 'thanksgiving';
    case BuildingFund = 'building_fund';
    case Other = 'other';

}
