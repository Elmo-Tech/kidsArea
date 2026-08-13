<?php

namespace App\Exceptions\Payroll;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeePayrollNotBelongToPeriodException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_PAYROLL_NOT_BELONG_TO_PERIOD',
            message: __('payroll.employee_payroll_not_belong_to_period'),
            status: HttpStatusCode::NOT_FOUND
        );
    }
}
