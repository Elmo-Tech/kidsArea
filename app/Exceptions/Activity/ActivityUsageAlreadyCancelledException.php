<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityUsageAlreadyCancelledException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_USAGE_ALREADY_CANCELLED',
            message: __('activity.activity_usage_already_cancelled'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
