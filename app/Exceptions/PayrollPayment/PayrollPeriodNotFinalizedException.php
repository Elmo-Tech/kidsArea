<?php

namespace App\Exceptions\PayrollPayment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollPeriodNotFinalizedException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYROLL_PERIOD_NOT_FINALIZED',
            message: __('payroll.period_not_finalized'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
