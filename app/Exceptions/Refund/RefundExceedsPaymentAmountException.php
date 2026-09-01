<?php

declare(strict_types=1);

namespace App\Exceptions\Refund;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class RefundExceedsPaymentAmountException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'REFUND_EXCEEDS_PAYMENT_AMOUNT',
            message: __('refund.exceeds_payment_amount'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
