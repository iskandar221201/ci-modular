<?php

namespace App\Shared\Exceptions;

class ValidationException extends ServiceException
{
    public function __construct(array $errors, string $message = 'Validation failed')
    {
        parent::__construct($message, 422, $errors);
    }
}
