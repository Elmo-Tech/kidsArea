<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashShiftNoCashToTransferException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_SHIFT_NO_CASH_TO_TRANSFER',
            message: __('cash.shift_no_cash_to_transfer'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
