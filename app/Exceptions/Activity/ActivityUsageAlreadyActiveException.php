<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityUsageAlreadyActiveException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_USAGE_ALREADY_ACTIVE',
            message: __('activity.activity_usage_already_active'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
