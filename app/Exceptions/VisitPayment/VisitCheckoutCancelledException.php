<?php

declare(strict_types=1);

namespace App\Exceptions\VisitPayment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCheckoutCancelledException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CHECKOUT_CANCELLED',
            message: __('visit.checkout_cancelled'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
