<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class MainCashRegisterAlreadyExistsException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'MAIN_CASH_REGISTER_ALREADY_EXISTS',
            message: __('cash.main_register_already_exists'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
