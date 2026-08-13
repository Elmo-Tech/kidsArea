<?php

namespace App\Exceptions\EmployeeSession;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeSessionCompletedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_SESSION_COMPLETED',
            message: __('employee.session_completed'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
