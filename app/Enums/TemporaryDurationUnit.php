<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum TemporaryDurationUnit: string
{
    use HasTranslatableLabel;

    case Month = 'month';
    case Year = 'year';

}
