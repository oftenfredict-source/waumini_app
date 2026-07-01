<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum MaritalStatus: string
{
    use HasTranslatableLabel;

    case Single = 'single';
    case Married = 'married';
    case Divorced = 'divorced';
    case Widowed = 'widowed';

}
