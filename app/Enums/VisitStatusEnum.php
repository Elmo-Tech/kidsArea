<?php

namespace App\Enums;

enum VisitStatusEnum: int
{
    case OPEN = 0;
    case CLOSED = 1;
    case CANCELLED = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
