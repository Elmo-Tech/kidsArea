<?php

declare(strict_types=1);

namespace App\Exceptions\Visit;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitPaymentAmountRequiredException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_PAYMENT_AMOUNT_REQUIRED',
            message: __('visit.payment_amount_required'),
            status: HttpStatusCode::UNPROCESSABLE_ENTITY
        );
    }
}
