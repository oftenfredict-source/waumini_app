<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum MemberType: string
{
    use HasTranslatableLabel;

    case Father = 'father';
    case Mother = 'mother';
    case Independent = 'independent';

}
