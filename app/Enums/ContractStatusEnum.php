<?php

namespace App\Enums;

enum ContractStatusEnum: int
{
    case DRAFT = 0;
    case ACTIVE = 1;
    case EXPIRED = 2;
    case TERMINATED = 3;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
