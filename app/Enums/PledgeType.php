<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum PledgeType: string
{
    use HasTranslatableLabel;

    case Building = 'building';
    case Mission = 'mission';
    case Special = 'special';
    case General = 'general';
    case Other = 'other';

}
