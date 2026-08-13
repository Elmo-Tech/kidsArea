<?php

namespace App\Enums;

enum LeavePayrollEffectEnum: int
{
    case PAID = 0;
    case UNPAID = 1;
    case NO_EFFECT = 2;
    case PAID_BASIC_ONLY = 3;
    case UNPAID_BASIC = 4;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
