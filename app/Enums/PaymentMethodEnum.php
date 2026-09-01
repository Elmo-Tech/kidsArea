<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethodEnum: int
{
    case CASH = 0;
    case CARD = 1;
    case BANK_TRANSFER = 2;
    case INSTAPAY = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
