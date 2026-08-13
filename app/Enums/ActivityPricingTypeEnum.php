<?php

namespace App\Enums;

enum ActivityPricingTypeEnum: int
{
    case HOURLY = 0;
    case SESSION = 1;
    case SUBSCRIPTION = 2;
    case PACKAGE = 3;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
