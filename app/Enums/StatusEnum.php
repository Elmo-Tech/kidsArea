<?php

namespace App\Enums;

enum StatusEnum: int
{
    case INACTIVE = 0;
    case ACTIVE = 1;

    public function label(): string
    {
        return match ($this) {
            self::INACTIVE => __('enums.status.inactive'),
            self::ACTIVE => __('enums.status.active'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
