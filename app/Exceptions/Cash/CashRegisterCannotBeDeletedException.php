<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashRegisterCannotBeDeletedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_REGISTER_CANNOT_BE_DELETED',
            message: __('cash.cannot_be_deleted'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
