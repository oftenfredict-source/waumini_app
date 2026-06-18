<?php

namespace App\Enums;

enum FinancePaymentMethod: string
{
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case MobileMoney = 'mobile_money';
    case Cheque = 'cheque';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank Transfer',
            self::MobileMoney => 'Mobile Money',
            self::Cheque => 'Cheque',
        };
    }
}
