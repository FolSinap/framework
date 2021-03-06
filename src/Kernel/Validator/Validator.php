<?php

namespace FW\Kernel\Validator;

use FW\Kernel\Storage\Session;
use FW\Kernel\Validator\Rules\IRule;

class Validator
{
    //todo: add option 'stop on first error'
    protected array $rules;

    /**
     * @param IRule[][] $rules
     */
    public function __construct(array $rules = [])
    {
        $this->rules = $rules;
    }

    public function validateData(array $data): bool
    {
        $errorMessages = [];

        foreach ($this->rules as $field => $rules) {
            foreach ($rules as $rule) {
                if (!$rule->validate($data[$field] ?? null)) {
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
