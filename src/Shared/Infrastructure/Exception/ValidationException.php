<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Exception;

use RuntimeException;

/**
 * Exception thrown when validation fails
 */
class ValidationException extends RuntimeException
{
    private array $errors;

    public function __construct(array $errors = [], string $message = 'Validation failed', int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

