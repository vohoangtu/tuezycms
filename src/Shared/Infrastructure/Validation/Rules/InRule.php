<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation\Rules;

/**
 * In array validation rule
 */
class InRule implements ValidationRule
{
    private array $allowed;

    public function __construct(array $allowed)
    {
        $this->allowed = $allowed;
    }

    public function validate(mixed $value, array $data = []): bool
    {
        return in_array($value, $this->allowed, true);
    }

    public function getMessage(string $field): string
    {
        $allowed = implode(', ', $this->allowed);
        return "The {$field} must be one of: {$allowed}.";
    }
}

