<?php

namespace App\Exceptions\EmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class UserHasNoEmployeeException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'USER_HAS_NO_EMPLOYEE',
            message: __('employee.user_has_no_employee'),
            status: HttpStatusCode::FORBIDDEN
        );
    }
}
