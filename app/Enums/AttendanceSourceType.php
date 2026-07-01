<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum AttendanceSourceType: string
{
    use HasTranslatableLabel;

    case ChurchService = 'church_service';
    case SpecialEvent = 'special_event';

}
