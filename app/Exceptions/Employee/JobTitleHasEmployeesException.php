<?php

namespace App\Exceptions\Employee;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class JobTitleHasEmployeesException
    extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'JOB_TITLE_HAS_EMPLOYEES',
            message: __('employee.job_title_has_employees'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
