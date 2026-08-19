<?php

declare(strict_types=1);

namespace App\Exceptions\VisitCheckout;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitCheckoutNotEditableException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_CHECKOUT_NOT_EDITABLE',
            message: __('visit.chekout_not_editable'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
