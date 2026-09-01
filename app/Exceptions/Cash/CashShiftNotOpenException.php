<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashShiftNotOpenException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_SHIFT_NOT_OPEN',
            message: __('cash.shift_not_open'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
