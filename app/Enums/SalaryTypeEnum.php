<?php

namespace App\Enums;

enum SalaryTypeEnum: int
{
    case MONTHLY = 0;
    case HOURLY = 1;
    case SESSION = 2;
    case MONTHLY_PLUS_SESSION = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
