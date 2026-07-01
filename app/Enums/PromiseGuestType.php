<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum PromiseGuestType: string
{
    use HasTranslatableLabel;

    case Promised = 'promised';
    case Temporary = 'temporary';

}
