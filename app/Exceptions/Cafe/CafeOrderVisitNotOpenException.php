<?php

declare(strict_types=1);

namespace App\Exceptions\Cafe;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CafeOrderVisitNotOpenException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CAFE_ORDER_VISIT_NOT_OPEN',
            message: __('cafe.order_visit_not_open'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
