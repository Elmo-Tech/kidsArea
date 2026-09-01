<?php

declare(strict_types=1);

namespace App\Exceptions\Refund;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class RefundCashShiftRequiredException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'REFUND_CASH_SHIFT_REQUIRED',
            message: __('refund.cash_shift_required'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
