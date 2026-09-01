<?php

declare(strict_types=1);

namespace App\Exceptions\Expense;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class ExpenseCashShiftRequiredException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'EXPENSE_CASH_SHIFT_REQUIRED',
            message: __('expense.cash_shift_required'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
