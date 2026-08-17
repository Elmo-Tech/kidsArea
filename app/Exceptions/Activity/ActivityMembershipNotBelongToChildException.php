<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityMembershipNotBelongToChildException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_MEMBERSHIP_NOT_BELONG_TO_CHILD',
            message: __('activity.membership_not_belong_to_child'),
            status: HttpStatusCode::UNPROCESSABLE_ENTITY
        );
    }
}
