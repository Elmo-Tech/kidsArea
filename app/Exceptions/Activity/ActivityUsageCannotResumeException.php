<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityUsageCannotResumeException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_USAGE_CANNOT_RESUME',
            message: __('activity.activity_usage_cannot_resume'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
