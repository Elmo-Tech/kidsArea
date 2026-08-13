<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class InvalidActivityPricingPlanDataException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'INVALID_ACTIVITY_PRICING_PLAN_DATA',
            message: __('activity.pricing_plan_invalid_data'),
            status: HttpStatusCode::UNPROCESSABLE_ENTITY
        );
    }
}
