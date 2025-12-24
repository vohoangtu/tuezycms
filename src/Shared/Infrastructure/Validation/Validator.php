<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation;

/**
 * Validator interface
 */
interface Validator
{
    /**
     * Validate data
     *
     * @param array $data
     * @return array Array of errors (empty if valid)
     */
    public function validate(array $data): array;
}

