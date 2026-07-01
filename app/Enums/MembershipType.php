<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum MembershipType: string
{
    use HasTranslatableLabel;

    case Permanent = 'permanent';
    case Temporary = 'temporary';

}
