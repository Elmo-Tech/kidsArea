<?php

namespace App\Exceptions\PayrollPayment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeePayrollAlreadyPaidException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_PAYROLL_ALREADY_PAID',
            message: __('payroll.already_paid'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
