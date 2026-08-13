<?php

namespace App\Enums;

enum PayrollPeriodStatusEnum: int
{
    case DRAFT = 0;
    case FINALIZED = 1;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
