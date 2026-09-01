<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashRegisterInactiveException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_REGISTER_INACTIVE',
            message: __('cash.register_inactive'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
