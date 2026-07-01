<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum PledgePaymentFrequency: string
{
    use HasTranslatableLabel;

    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annually = 'annually';
    case OneTime = 'one_time';

}
