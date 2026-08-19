<?php

declare(strict_types=1);

namespace App\Enums;

enum VisitPaymentStatusEnum: int
{
    case UNPAID = 0;
    case PARTIALLY_PAID = 1;
    case PAID = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
