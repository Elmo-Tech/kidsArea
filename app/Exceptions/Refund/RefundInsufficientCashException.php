<?php

declare(strict_types=1);

namespace App\Exceptions\Refund;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class RefundInsufficientCashException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'REFUND_INSUFFICIENT_CASH',
            message: __('refund.insufficient_cash'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
