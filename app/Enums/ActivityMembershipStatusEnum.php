<?php

namespace App\Enums;

enum ActivityMembershipStatusEnum: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;
    case EXPIRED = 2;
    case CANCELLED = 3;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
