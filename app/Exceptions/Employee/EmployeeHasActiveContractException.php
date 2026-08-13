<?php

namespace App\Exceptions\Employee;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeHasActiveContractException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_HAS_ACTIVE_CONTRACT',
            message: __('employee.employee_has_active_contract'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
