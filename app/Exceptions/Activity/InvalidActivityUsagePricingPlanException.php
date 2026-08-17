<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class InvalidActivityUsagePricingPlanException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'INVALID_ACTIVITY_USAGE_PRICING_PLAN',
            message: __('activity.activity_usage_invalid_pricing_plan'),
            status: HttpStatusCode::UNPROCESSABLE_ENTITY
        );
    }
}
