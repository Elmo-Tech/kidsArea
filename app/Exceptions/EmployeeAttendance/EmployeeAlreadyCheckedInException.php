<?php

namespace App\Exceptions\EmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeAlreadyCheckedInException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_ALREADY_CHECKED_IN',
            message: __('employee.already_checked_in'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
