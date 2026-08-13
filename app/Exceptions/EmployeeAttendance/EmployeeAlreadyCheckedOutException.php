<?php

namespace App\Exceptions\EmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeAlreadyCheckedOutException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_ALREADY_CHECKED_OUT',
            message: __('already_checked_out'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
