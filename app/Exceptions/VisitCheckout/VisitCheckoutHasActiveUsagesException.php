<?php

declare(strict_types=1);

namespace App\Exceptions\VisitCheckout;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCheckoutHasActiveUsagesException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CHECKOUT_HAS_ACTIVE_USAGES',
            message: __('visit.checkout_has_active_usages'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
