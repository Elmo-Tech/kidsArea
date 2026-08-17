<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityUsageCannotPauseException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_USAGE_CANNOT_PAUSE',
            message: __('activity.activity_usage_cannot_pause'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
