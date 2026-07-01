<?php

namespace App\Enums;

use App\Enums\Concerns\HasTranslatableLabel;

enum FinancePaymentMethod: string
{
    use HasTranslatableLabel;

    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case MobileMoney = 'mobile_money';
    case Cheque = 'cheque';

}
