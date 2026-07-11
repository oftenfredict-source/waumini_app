<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum ServiceCoordinatorType: string
{
    use HasTranslatableLabel;

    case Member = 'member';
    case Guest = 'guest';
}
