<?php

declare(strict_types=1);

namespace App\Exceptions\Payment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ActivityUsageNotClosedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'ACTIVITY_USAGE_NOT_CLOSED',
            message: __('payment.activity_usage_not_closed'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
