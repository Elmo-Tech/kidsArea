<?php

namespace App\Exceptions\Employee;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeAttendanceAlreadyExistsException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_ATTENDANCE_ALREADY_EXISTS',
            message: __('employee.attendance_already_exists'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
