<?php

namespace App\Exceptions\Payroll;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollPeriodAlreadyExistsException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYROLL_PERIOD_ALREADY_EXISTS',
            message: __('payroll.period_already_exists'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
