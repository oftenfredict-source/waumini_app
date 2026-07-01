<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum CelebrationSource: string
{
    use HasTranslatableLabel;

    case Auto = 'auto';
    case Manual = 'manual';

}
