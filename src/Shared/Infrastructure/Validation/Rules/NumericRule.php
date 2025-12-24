<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation\Rules;

/**
 * Numeric validation rule
 */
class NumericRule implements ValidationRule
{
    public function validate(mixed $value, array $data = []): bool
    {
        return is_numeric($value);
    }

    public function getMessage(string $field): string
    {
        return "The {$field} must be a number.";
    }
}

