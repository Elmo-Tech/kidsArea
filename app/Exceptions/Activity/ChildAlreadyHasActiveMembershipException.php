<?php

declare(strict_types=1);

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ChildAlreadyHasActiveMembershipException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CHILD_ALREADY_HAS_ACTIVE_MEMBERSHIP',
            message: __('activity.child_already_has_active_membership'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
