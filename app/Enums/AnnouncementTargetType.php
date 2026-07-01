<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum AnnouncementTargetType: string
{
    use HasTranslatableLabel;

    case All = 'all';
    case Specific = 'specific';
    case Department = 'department';

}
