<?php

namespace App\Enums;

enum EmployeeSessionStatusEnum: int
{
    case PENDING = 0;
    case COMPLETED = 1;
    case CANCELLED = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
