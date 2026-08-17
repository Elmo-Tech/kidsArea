<?php

declare(strict_types=1);

namespace App\Exceptions\Visit;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitAlreadyClosedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_ALREADY_CLOSED',
            message: __('visit.already_closed'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
