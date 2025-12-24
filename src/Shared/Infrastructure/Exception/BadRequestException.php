<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Exception;

use RuntimeException;

/**
 * Exception thrown for bad requests
 */
class BadRequestException extends RuntimeException
{
    public function __construct(string $message = 'Bad Request', int $code = 400, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

