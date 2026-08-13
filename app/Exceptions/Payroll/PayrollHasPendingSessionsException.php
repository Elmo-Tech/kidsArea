<?php

namespace App\Exceptions\Payroll;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollHasPendingSessionsException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PAYROLL_HAS_PENDING_SESSIONS',
            message: __('payroll.has_pending_sessions'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
