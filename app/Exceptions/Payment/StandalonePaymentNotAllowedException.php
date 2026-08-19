<?php

declare(strict_types=1);

namespace App\Exceptions\Payment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class StandalonePaymentNotAllowedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'STANDALONE_PAYMENT_NOT_ALLOWED',
            message: __('payment.standalone_not_allowed'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
