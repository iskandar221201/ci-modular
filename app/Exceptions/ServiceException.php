<?php

namespace App\Exceptions;

class ServiceException extends \RuntimeException
{
    protected array $errors = [];
    protected int $statusCode = 400;

    public function __construct(string $message, int $statusCode = 400, array $errors = [], ?\Throwable $previous = null)
    {
        $this->statusCode = $statusCode;
        $this->errors     = $errors;

        parent::__construct($message, 0, $previous);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
