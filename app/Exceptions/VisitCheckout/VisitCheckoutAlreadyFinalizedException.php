<?php

declare(strict_types=1);

namespace App\Exceptions\VisitCheckout;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCheckoutAlreadyFinalizedException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CHECKOUT_ALREADY_FINALIZED',
            message: __('visit.checkout_already_finalized'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
