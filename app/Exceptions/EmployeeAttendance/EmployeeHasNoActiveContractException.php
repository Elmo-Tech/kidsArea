<?php

namespace App\Exceptions\EmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeHasNoActiveContractException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_HAS_NO_ACTIVE_CONTRACT',
            message: __('employee.no_active_contract'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
