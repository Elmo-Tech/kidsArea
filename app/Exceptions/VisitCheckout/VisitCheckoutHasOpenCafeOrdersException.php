<?php

declare(strict_types=1);

namespace App\Exceptions\VisitCheckout;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCheckoutHasOpenCafeOrdersException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CHECKOUT_HAS_OPEN_CAFE_ORDERS',
            message: __('visit.check_has_open_cafe_orders'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
