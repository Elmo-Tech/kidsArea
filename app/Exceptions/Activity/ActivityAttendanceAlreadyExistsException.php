<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityAttendanceAlreadyExistsException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_ATTENDANCE_ALREADY_EXISTS',
            message: __('activity.already_exists'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
