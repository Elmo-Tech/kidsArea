<?php

declare(strict_types=1);

namespace App\Exceptions\Cash;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class CashRegisterHasPendingTransferException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'CASH_REGISTER_HAS_PENDING_TRANSFER',
            message: __('cash.register_has_pending_transfer'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
