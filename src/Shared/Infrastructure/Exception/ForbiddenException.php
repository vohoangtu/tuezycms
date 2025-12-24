<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Exception;

use RuntimeException;

/**
 * Exception thrown when user is not authorized to perform action
 */
class ForbiddenException extends RuntimeException
{
    public function __construct(string $message = 'Forbidden', int $code = 403, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

