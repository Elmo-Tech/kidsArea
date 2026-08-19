<?php

declare(strict_types=1);

namespace App\Exceptions\Cafe;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CafeOrderNotEditableException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CAFE_ORDER_NOT_EDITABLE',
            message: __('cafe.order_not_editable'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
