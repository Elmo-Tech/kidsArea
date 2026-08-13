<?php

namespace App\Enums;

enum ActivitySessionStatusEnum: int
{
    case SCHEDULED = 0;
    case COMPLETED = 1;
    case CANCELLED = 2;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
