<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashShiftNotOwnedByCurrentUserException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_SHIFT_NOT_OWNED_BY_CURRENT_USER',
            message: __('cash.shift_not_owned_by_current_user'),
            status: HttpStatusCode::FORBIDDEN
        );
    }
}
