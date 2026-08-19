<?php

declare(strict_types=1);

namespace App\Exceptions\VisitPayment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitPaymentExceedsRemainingAmountException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_PAYMENT_EXCEEDS_REMAINING_AMOUNT',
            message: __('visit.exceeds_remaining_amount'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
