<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation\Rules;

/**
 * String validation rule
 */
class StringRule implements ValidationRule
{
    public function validate(mixed $value, array $data = []): bool
    {
        return is_string($value);
    }

    public function getMessage(string $field): string
    {
        return "The {$field} must be a string.";
    }
}

