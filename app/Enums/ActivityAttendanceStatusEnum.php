<?php

namespace App\Enums;

enum ActivityAttendanceStatusEnum: int
{
    case ABSENT = 0;
    case PRESENT = 1;
    case EXCUSED = 2;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
