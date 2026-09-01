<?php

declare(strict_types=1);

namespace App\Enums;

enum CashTransactionTypeEnum: int
{
    case IN = 0;
    case OUT = 1;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
