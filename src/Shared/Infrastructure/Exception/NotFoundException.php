<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Exception;

use RuntimeException;

/**
 * Exception thrown when a resource is not found
 */
class NotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Resource not found', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

