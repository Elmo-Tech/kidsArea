<?php

namespace App\Exceptions\Employee;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class PermissionNotPendingException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'PERMISSION_NOT_PENDING',
            message: __('employee.permission_not_pending'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
