<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation\Rules;

/**
 * Minimum value/length validation rule
 */
class MinRule implements ValidationRule
{
    private int $min;

    public function __construct(int $min)
    {
        $this->min = $min;
    }

    public function validate(mixed $value, array $data = []): bool
    {
        if (is_string($value)) {
            return mb_strlen($value) >= $this->min;
        }

        if (is_numeric($value)) {
            return (float)$value >= $this->min;
        }

        if (is_array($value)) {
            return count($value) >= $this->min;
        }

        return false;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} must be at least {$this->min}.";
    }
}

