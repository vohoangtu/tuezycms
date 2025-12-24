<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation\Rules;

use function blank;

/**
 * Required field validation rule
 */
class RequiredRule implements ValidationRule
{
    public function validate(mixed $value, array $data = []): bool
    {
        return !blank($value);
    }

    public function getMessage(string $field): string
    {
        return "The {$field} field is required.";
    }
}

