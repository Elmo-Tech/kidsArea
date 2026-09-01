<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashTransferInsufficientBalanceException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_TRANSFER_INSUFFICIENT_BALANCE',
            message: __('cash.transfer_insufficient_balance'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
