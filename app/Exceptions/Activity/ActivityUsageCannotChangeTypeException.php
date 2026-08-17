<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityUsageCannotChangeTypeException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_USAGE_CANNOT_CHANGE_TYPE',
            message: __('activity.activity_usage_cannot_change_type'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
