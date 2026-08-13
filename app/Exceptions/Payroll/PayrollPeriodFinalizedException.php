<?php

namespace App\Exceptions\Payroll;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollPeriodFinalizedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYROLL_PERIOD_FINALIZED',
            message: __('payroll.period_finalized'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
