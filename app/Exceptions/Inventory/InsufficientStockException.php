<?php

declare(strict_types=1);

namespace App\Exceptions\Inventory;

use App\Enums\HttpStatusCode;
use App\Exceptions\BusinessLogicException;

class InsufficientStockException extends BusinessLogicException
{
    public function __construct()
    {
        parent::__construct(
            errorCode: 'INSUFFICIENT_STOCK',
            message: __('inventory.insufficient_stock'),
            status: HttpStatusCode::CONFLICT
        );
    }
}
