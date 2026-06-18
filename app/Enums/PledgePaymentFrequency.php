<?php

namespace App\Enums;

enum PledgePaymentFrequency: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Annually = 'annually';
    case OneTime = 'one_time';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::Annually => 'Annually',
            self::OneTime => 'One-time',
        };
    }
}
