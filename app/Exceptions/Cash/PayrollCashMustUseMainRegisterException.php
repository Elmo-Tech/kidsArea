<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollCashMustUseMainRegisterException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYROLL_CASH_MUST_USE_MAIN_REGISTER',
            message: __('cash.payroll_must_use_main_register'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
