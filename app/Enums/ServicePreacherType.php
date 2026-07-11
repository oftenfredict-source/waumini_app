<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum ServicePreacherType: string
{
    use HasTranslatableLabel;

    case Pastor = 'pastor';
    case Leader = 'leader';
    case Member = 'member';
    case Guest = 'guest';
}
