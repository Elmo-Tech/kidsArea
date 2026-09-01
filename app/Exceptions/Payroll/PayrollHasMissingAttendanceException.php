<?php

declare(strict_types=1);

namespace App\Exceptions\Payroll;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PayrollHasMissingAttendanceException extends BusinessLogicException
{
    public function __construct(
        string $employeeName,
        string $date
    ) {
        parent::__construct(
            errorCode: 'PAYROLL_HAS_MISSING_ATTENDANCE',
            message: __('employee.has_missing_attendance', [
                'employee' => $employeeName,
                'date' => $date,
            ]),
            status: HttpStatusCode::CONFLICT
        );
    }
}
