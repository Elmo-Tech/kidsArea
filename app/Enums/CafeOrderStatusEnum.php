<?php

namespace App\Enums;

enum CafeOrderStatusEnum: int
{
    case DRAFT = 0;
    case CONFIRMED = 1;
    case COMPLETED = 2;
    case CANCELLED = 3;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
