<?php

declare(strict_types=1);

namespace App\Exceptions\ActivitySessionEmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class EmployeeNotAssignedToSessionException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EMPLOYEE_NOT_ASSIGNED_TO_SESSION',
            message: __('activity_session_employee_attendance.employee_not_assigned'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
