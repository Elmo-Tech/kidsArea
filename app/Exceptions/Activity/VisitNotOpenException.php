<?php

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitNotOpenException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_NOT_OPEN',
            message: __('activity.activity_usage_visit_not_open'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
