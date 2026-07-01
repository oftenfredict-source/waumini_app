<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum BereavementContributionType: string
{
    use HasTranslatableLabel;

    case Individual = 'individual';
    case FamilyWide = 'family_wide';

}
