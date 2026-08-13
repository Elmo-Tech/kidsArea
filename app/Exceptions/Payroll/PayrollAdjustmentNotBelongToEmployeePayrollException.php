<?php

namespace App\Exceptions\Payroll;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollAdjustmentNotBelongToEmployeePayrollException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYROLL_ADJUSTMENT_NOT_BELONG_TO_EMPLOYEE_PAYROLL',
            message: __('payroll.adjustment_not_belong_to_employee_payroll'),
            status: HttpStatusCode::NOT_FOUND
        );
    }
}
