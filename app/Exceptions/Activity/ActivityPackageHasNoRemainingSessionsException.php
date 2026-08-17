<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityPackageHasNoRemainingSessionsException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_PACKAGE_HAS_NO_REMAINING_SESSIONS',
            message: __('activity.package_has_no_remaining_sessions'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
