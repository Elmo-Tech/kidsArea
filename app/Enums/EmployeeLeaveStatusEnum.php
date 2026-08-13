<?php

namespace App\Enums;

enum EmployeeLeaveStatusEnum: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
