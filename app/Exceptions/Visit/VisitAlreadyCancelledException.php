<?php

declare(strict_types=1);

namespace App\Exceptions\Visit;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitAlreadyCancelledException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_ALREADY_CANCELLED',
            message: __('visit.already_cancelled'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
