<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class InvalidActivityMembershipPricingPlanException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'INVALID_ACTIVITY_MEMBERSHIP_PRICING_PLAN',
            message: __('activity.invalid_pricing_plan'),
            status: HttpStatusCode::UNPROCESSABLE_ENTITY
        );
    }
}
