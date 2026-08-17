<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityMembershipNotActiveException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_MEMBERSHIP_NOT_ACTIVE',
            message: __('activity.membership_not_active'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
