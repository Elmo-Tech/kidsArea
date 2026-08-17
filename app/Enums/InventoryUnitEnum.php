<?php

namespace App\Enums;

enum InventoryUnitEnum: int
{
    case PIECE = 0;
    case MILLILITER = 1;
    case LITER = 2;
    case GRAM = 3;
    case KILOGRAM = 4;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
