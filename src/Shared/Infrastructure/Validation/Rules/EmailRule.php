<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation\Rules;

/**
 * Email validation rule
 */
class EmailRule implements ValidationRule
{
    public function validate(mixed $value, array $data = []): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} must be a valid email address.";
    }
}

