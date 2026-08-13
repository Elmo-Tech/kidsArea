<?php

namespace App\Enums;

enum DurationUnitEnum: int
{
    case DAY = 0;
    case WEEK = 1;
    case MONTH = 2;
    case YEAR = 3;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
