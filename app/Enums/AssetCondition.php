<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum AssetCondition: string
{
    use HasTranslatableLabel;

    case Excellent = 'excellent';
    case Good = 'good';
    case Fair = 'fair';
    case Poor = 'poor';

}
