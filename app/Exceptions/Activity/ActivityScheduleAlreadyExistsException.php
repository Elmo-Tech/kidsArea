<?php

declare(strict_types=1);

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityScheduleAlreadyExistsException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_SCHEDULE_ALREADY_EXISTS',
            message: __('activity.schedule_already_exists'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
