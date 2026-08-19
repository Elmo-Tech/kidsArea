<?php

declare(strict_types=1);

namespace App\Exceptions\VisitPayment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCheckoutAlreadyPaidException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CHECKOUT_ALREADY_PAID',
            message: __('visit.checkout_already_paid'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
