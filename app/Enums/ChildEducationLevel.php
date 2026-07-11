<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum ChildEducationLevel: string
{
    use HasTranslatableLabel;

    case Nursery = 'nursery';
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Advance = 'advance';
    case University = 'university';
}
