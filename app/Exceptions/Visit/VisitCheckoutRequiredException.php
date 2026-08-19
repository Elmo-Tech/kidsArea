<?php

declare(strict_types=1);

namespace App\Exceptions\Visit;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCheckoutRequiredException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CHECKOUT_REQUIRED',
            message: __('visit.checkout_required'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
