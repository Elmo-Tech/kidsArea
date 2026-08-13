<?php

namespace App\Exceptions\Payroll;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollHasIncompleteAttendanceException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYROLL_HAS_INCOMPLETE_ATTENDANCE',
            message: __('payroll.has_incomplete_attendance'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
