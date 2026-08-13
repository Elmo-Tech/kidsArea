<?php

namespace App\Exceptions\EmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeNotCheckedInException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_NOT_CHECKED_IN',
            message: __('employee.not_checked_in'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
