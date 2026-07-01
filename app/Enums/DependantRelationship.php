<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum DependantRelationship: string
{
    use HasTranslatableLabel;

    case Child = 'child';
    case Relative = 'relative';
    case Other = 'other';

}
