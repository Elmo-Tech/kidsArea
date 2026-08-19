<?php

declare(strict_types=1);

namespace App\Exceptions\Payment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PaymentExceedsRemainingAmountException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYMENT_EXCEEDS_REMAINING_AMOUNT',
            message: __('payment.exceeds_remaining_amount'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
