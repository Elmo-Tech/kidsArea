<?php

declare(strict_types=1);

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityMembershipAlreadyRenewedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_MEMBERSHIP_ALREADY_RENEWED',
            message: __('activity.membership_already_renewed'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
