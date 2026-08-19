<?php

declare(strict_types=1);

namespace App\Exceptions\Visit;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitNotFullyPaidException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_NOT_FULLY_PAID',
            message: __('visit.not_fully_paid'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
