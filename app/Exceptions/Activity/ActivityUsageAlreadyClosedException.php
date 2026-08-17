<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityUsageAlreadyClosedException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_USAGE_ALREADY_CLOSED',
            message: __('activity.activity_usage_already_closed'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
