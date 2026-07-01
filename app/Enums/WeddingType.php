<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum WeddingType: string
{
    use HasTranslatableLabel;

    case Church = 'church';
    case Civil = 'civil';
    case Traditional = 'traditional';
    case Customary = 'customary';
    case Other = 'other';

}
