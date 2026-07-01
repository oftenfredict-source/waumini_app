<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum BudgetType: string
{
    use HasTranslatableLabel;

    case Annual = 'annual';
    case Other = 'other';

}
