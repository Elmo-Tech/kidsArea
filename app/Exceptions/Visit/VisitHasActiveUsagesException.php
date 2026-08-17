<?php

declare(strict_types=1);

namespace App\Exceptions\Visit;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class VisitHasActiveUsagesException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'VISIT_HAS_ACTIVE_USAGES',
            message: __('visit.has_active_usages'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
