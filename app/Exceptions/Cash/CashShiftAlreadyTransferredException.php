<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashShiftAlreadyTransferredException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_SHIFT_ALREADY_TRANSFERRED',
            message: __('cash.shift_already_transferred'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
