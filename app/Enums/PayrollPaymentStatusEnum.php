<?php

declare(strict_types=1);

namespace App\Enums;

enum PayrollPaymentStatusEnum: int
{
    case UNPAID = 0;

    case PARTIALLY_PAID = 1;

    case PAID = 2;
}
