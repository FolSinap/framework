<?php

namespace Fwt\Framework\Kernel\Validator;

use Fwt\Framework\Kernel\Validator\Rules\RuleInterface;

class Validator
{
    protected array $rules;

    /**
     * @param RuleInterface[][] $rules
     */
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public function validate(array $data): array
    {
        $errorMessages = [];

        foreach ($this->rules as $field => $rules) {
            foreach ($rules as $rule) {
                if (!$rule->validate($data[$field])) {
                    $errorMessages[$field][] = $rule->getErrorMessage();
                }
            }
        }

        return $errorMessages;
    }
}
