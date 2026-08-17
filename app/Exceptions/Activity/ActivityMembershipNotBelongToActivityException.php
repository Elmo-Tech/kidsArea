<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityMembershipNotBelongToActivityException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_MEMBERSHIP_NOT_BELONG_TO_ACTIVITY',
            message: __('activity.membership_not_belong_to_activity'),
            status: HttpStatusCode::UNPROCESSABLE_ENTITY
        );
    }
}
