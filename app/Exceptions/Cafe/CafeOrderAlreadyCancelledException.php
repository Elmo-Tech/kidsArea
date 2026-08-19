<?php

declare(strict_types=1);

namespace App\Exceptions\Cafe;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CafeOrderAlreadyCancelledException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CAFE_ORDER_ALREADY_CANCELLED',
            message: __('cafe.order_already_cancelled'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
