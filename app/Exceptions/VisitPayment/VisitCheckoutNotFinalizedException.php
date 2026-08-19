<?php

declare(strict_types=1);

namespace App\Exceptions\VisitPayment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCheckoutNotFinalizedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CHECKOUT_NOT_FINALIZED',
            message: __('visit.checkout_not_finalized'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
