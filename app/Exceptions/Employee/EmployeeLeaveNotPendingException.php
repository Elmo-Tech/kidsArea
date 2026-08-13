<?php

namespace App\Exceptions\Employee;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeLeaveNotPendingException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_LEAVE_NOT_PENDING',
            message: __('employee.leave_not_pending'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
