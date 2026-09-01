<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashRegisterAlreadyHasOpenShiftException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_REGISTER_ALREADY_HAS_OPEN_SHIFT',
            message: __('cash.register_has_open_shift'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
