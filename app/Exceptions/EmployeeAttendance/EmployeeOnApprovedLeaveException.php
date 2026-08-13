<?php

namespace App\Exceptions\EmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeOnApprovedLeaveException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_ON_APPROVED_LEAVE',
            message: __('employee.on_approved_leave'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
