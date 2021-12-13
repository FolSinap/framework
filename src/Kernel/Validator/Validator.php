<?php

namespace Fwt\Framework\Kernel\Validator;

use Fwt\Framework\Kernel\Session\Session;
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

    public function validate(array $data): bool
    {
        $errorMessages = [];

        foreach ($this->rules as $field => $rules) {
            foreach ($rules as $rule) {
                if (!$rule->validate($data[$field])) {
                    $errorMessages[$field][] = $rule->getErrorMessage();
                }
            }
        }

        $this->saveToSession($errorMessages);

        return empty($errorMessages);
    }

    protected function saveToSession(array $messages, string $key = 'errors')
    {
        foreach ($messages as $nestedKey => $message) {
            Session::start()->set("$key.$nestedKey", $message);
        }
    }
}
