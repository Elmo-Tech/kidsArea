<?php

declare(strict_types=1);

namespace App\Enums;

enum CashShiftStatusEnum: int
{
    case OPEN = 0;
    case CLOSED = 1;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
