<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation\Rules;

/**
 * Maximum value/length validation rule
 */
class MaxRule implements ValidationRule
{
    private int $max;

    public function __construct(int $max)
    {
        $this->max = $max;
    }

    public function validate(mixed $value, array $data = []): bool
    {
        if (is_string($value)) {
            return mb_strlen($value) <= $this->max;
        }

        if (is_numeric($value)) {
            return (float)$value <= $this->max;
        }

        if (is_array($value)) {
            return count($value) <= $this->max;
        }

        return false;
    }

    public function getMessage(string $field): string
    {
        return "The {$field} must not exceed {$this->max}.";
    }
}

