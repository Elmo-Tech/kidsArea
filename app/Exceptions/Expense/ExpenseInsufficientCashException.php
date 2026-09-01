<?php

declare(strict_types=1);

namespace App\Exceptions\Expense;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ExpenseInsufficientCashException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EXPENSE_INSUFFICIENT_CASH',
            message: __('expense.insufficient_cash'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
