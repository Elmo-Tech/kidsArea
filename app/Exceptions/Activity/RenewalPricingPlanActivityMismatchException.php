<?php

declare(strict_types=1);

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class RenewalPricingPlanActivityMismatchException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'RENEWAL_PRICING_PLAN_ACTIVITY_MISMATCH',
            message: __('activity.renewal_pricing_plan_activity_mismatch'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
