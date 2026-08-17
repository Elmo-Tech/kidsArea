<?php

declare(strict_types=1);

namespace App\Exceptions\Visit;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCannotBeCancelledException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CANNOT_BE_CANCELLED',
            message: __('visit.cannot_be_cancelled'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
