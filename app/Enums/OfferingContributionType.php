<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum OfferingContributionType: string
{
    use HasTranslatableLabel;

    case Member = 'member';
    case General = 'general';

}
