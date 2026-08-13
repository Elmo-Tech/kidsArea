<?php

namespace App\Enums;

enum ActivityStatusEnum: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
