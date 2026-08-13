<?php

namespace App\Exceptions;

use App\Enums\HttpStatusCode;
use Exception;

class BusinessLogicException extends Exception
{
    public function __construct(
        public readonly string $errorCode,
        string $message = '',
        public readonly HttpStatusCode $status = HttpStatusCode::UNPROCESSABLE_ENTITY,
        public readonly mixed $errors = []
    ) {
        parent::__construct($message);
    }
}
