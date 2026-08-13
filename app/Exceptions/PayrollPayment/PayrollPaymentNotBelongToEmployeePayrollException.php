<?php

namespace App\Exceptions\PayrollPayment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollPaymentNotBelongToEmployeePayrollException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYROLL_PAYMENT_NOT_BELONG_TO_EMPLOYEE_PAYROLL',
            message: __('payroll.not_belong_to_employee_payroll'),
            status: HttpStatusCode::NOT_FOUND
        );
    }
}
