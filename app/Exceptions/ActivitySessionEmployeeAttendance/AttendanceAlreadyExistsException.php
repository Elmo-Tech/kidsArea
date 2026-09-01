<?php

declare(strict_types=1);

namespace App\Exceptions\ActivitySessionEmployeeAttendance;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class AttendanceAlreadyExistsException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'SESSION_EMPLOYEE_ATTENDANCE_ALREADY_EXISTS',
            message: __('activity_session_employee_attendance.already_exists'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
