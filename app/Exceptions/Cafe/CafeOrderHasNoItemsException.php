<?php

declare(strict_types=1);

namespace App\Exceptions\Cafe;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CafeOrderHasNoItemsException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CAFE_ORDER_HAS_NO_ITEMS',
            message: __('cafe.order_has_no_items'),
            status: HttpStatusCode::UNPROCESSABLE_ENTITY
        );
    }
}
