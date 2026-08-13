<?php

namespace App\Enums;

enum PayrollDeductionMethodEnum: int
{
    case NONE = 0;
    case BY_MINUTE = 1;
    case BY_DAY = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
