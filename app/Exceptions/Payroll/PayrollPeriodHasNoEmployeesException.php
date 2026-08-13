<?php

namespace App\Exceptions\Payroll;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollPeriodHasNoEmployeesException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYROLL_PERIOD_HAS_NO_EMPLOYEES',
            message: __('payroll.period_has_no_employees'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
