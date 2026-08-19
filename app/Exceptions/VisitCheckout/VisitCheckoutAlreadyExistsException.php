<?php

declare(strict_types=1);

namespace App\Exceptions\VisitCheckout;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCheckoutAlreadyExistsException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CHECKOUT_ALREADY_EXISTS',
            message: __('visit.checkout_already_exists'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
