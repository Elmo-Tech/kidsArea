<?php

namespace App\Enums;

enum ActivityUsageStatusEnum: int
{
    case ACTIVE = 0;
    case PAUSED = 1;
    case CLOSED = 2;
    case CANCELLED = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
