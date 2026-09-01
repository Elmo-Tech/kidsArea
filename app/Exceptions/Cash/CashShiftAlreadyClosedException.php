<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashShiftAlreadyClosedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_SHIFT_ALREADY_CLOSED',
            message: __('cash.shift_already_closed'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
