<?php

namespace App\Enums;

enum StockMovementTypeEnum: int
{
    case STOCK_IN = 0;
    case CONSUMPTION = 1;
    case ADJUSTMENT_IN = 2;
    case ADJUSTMENT_OUT = 3;
    case WASTE = 4;
    case RETURN = 5;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
