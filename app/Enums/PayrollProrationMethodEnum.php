<?php

namespace App\Enums;

enum PayrollProrationMethodEnum: int
{
    case FIXED_30_DAYS = 0;
    case CALENDAR_DAYS = 1;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
