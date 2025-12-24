<?php

declare(strict_types=1);

namespace Shared\Infrastructure\Validation;

use Shared\Infrastructure\Validation\Rules\ValidationRule;

/**
 * Base validator class
 */
abstract class BaseValidator implements Validator
{
    /**
     * Validation rules
     *
     * @var array<string, array<ValidationRule>>
     */
    protected array $rules = [];

    /**
     * Validate data
     *
     * @param array $data
     * @return array
     */
    public function validate(array $data): array
    {
        $errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                if (!$rule->validate($value, $data)) {
                    $errors[$field][] = $rule->getMessage($field);
                }
            }
        }

        return $errors;
    }

    /**
     * Get rules for a field
     *
     * @param string $field
     * @return array<ValidationRule>
     */
    protected function getRules(string $field): array
    {
        return $this->rules[$field] ?? [];
    }
}

