<?php

declare(strict_types=1);

namespace App\Exceptions\Activity;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CancelledMembershipCannotReceivePaymentException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CANCELLED_MEMBERSHIP_CANNOT_RECEIVE_PAYMENT',
            message: __('activity.cancelled_membership_cannot_receive_payment'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
