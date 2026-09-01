<?php

declare(strict_types=1);

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityMembershipNotRenewableException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_MEMBERSHIP_NOT_RENEWABLE',
            message: __('activity.membership_not_renewable'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
