<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashTransactionInsufficientBalanceException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_TRANSACTION_INSUFFICIENT_BALANCE',
            message: __('cash.transaction_insufficient_balance'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
