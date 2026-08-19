<?php

namespace App\Enums;

enum VisitCheckoutStatusEnum: int
{
    case DRAFT = 0;
    case FINALIZED = 1;
    case CANCELLED = 2;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
