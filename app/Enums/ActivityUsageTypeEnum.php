<?php

namespace App\Enums;

enum ActivityUsageTypeEnum: int
{
    case OPEN = 0;
    case FIXED_DURATION = 1;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
