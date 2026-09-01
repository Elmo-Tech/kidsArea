<?php

declare(strict_types=1);

namespace App\Enums;

enum ActivitySessionEmployeeAttendanceStatusEnum: int
{
    case PRESENT = 0;
    case ABSENT = 1;
    case LATE = 2;
    case EXCUSED = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
