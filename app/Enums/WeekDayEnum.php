<?php

namespace App\Enums;

enum WeekDayEnum: int
{
    case SUNDAY = 0;
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
