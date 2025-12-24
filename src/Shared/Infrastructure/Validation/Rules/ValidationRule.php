<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation\Rules;

/**
 * Base validation rule interface
 */
interface ValidationRule
{
    /**
     * Validate the value
     *
     * @param mixed $value
     * @param array $data
     * @return bool
     */
    public function validate(mixed $value, array $data = []): bool;

    /**
     * Get error message
     *
     * @param string $field
     * @return string
     */
    public function getMessage(string $field): string;
}

