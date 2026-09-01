<?php

declare(strict_types=1);

namespace App\Exceptions\Cafe;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CafeOrderDiscountExceedsSubtotalException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CAFE_ORDER_DISCOUNT_EXCEEDS_SUBTOTAL',
            message: __('cafe.order_discount_exceeds_subtotal'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
