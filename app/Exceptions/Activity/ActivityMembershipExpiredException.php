<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityMembershipExpiredException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_MEMBERSHIP_EXPIRED',
            message: __('activity.membership_expired'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
