<?php

declare(strict_types=1);

namespace App\Exceptions\Payment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayableAlreadyPaidException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYABLE_ALREADY_PAID',
            message: __('payment.already_paid'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
