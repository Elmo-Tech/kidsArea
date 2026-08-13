<?php

namespace App\Exceptions\PayrollPayment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class InvalidPayrollPaymentAmountException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'INVALID_PAYROLL_PAYMENT_AMOUNT',
            message: __('payroll.invalid_amount'),
            status: HttpStatusCode::UNPROCESSABLE_ENTITY
        );
    }
}
