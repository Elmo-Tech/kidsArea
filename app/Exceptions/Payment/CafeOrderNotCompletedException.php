<?php

declare(strict_types=1);

namespace App\Exceptions\Payment;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CafeOrderNotCompletedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CAFE_ORDER_NOT_COMPLETED',
            message: __('payment.cafe_order_not_completed'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
