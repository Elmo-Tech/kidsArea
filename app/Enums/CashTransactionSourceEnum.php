<?php

declare(strict_types=1);

namespace App\Enums;

enum CashTransactionSourceEnum: int
{
    case PAYMENT = 0;
    case PAYROLL_PAYMENT = 1;
    case EXPENSE = 2;
    case MANUAL = 3;
    case REFUND = 4;

    case TRANSFER = 5;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
