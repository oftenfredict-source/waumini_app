<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum DepartmentStatus: string
{
    use HasTranslatableLabel;

    case Active = 'active';
    case Inactive = 'inactive';

}
