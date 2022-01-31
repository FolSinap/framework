<?php

namespace Fwt\Framework\Kernel\Validator;

use Fwt\Framework\Kernel\Csrf\CsrfValidator;
use Fwt\Framework\Kernel\Session\Session;
use Fwt\Framework\Kernel\Validator\Rules\IRule;

class Validator
{
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

        //todo: move to rule class
        if (!$this->validateCsrf($data)) {
            $errorMessages[CsrfValidator::TOKEN_KEY][] = 'Invalid CSRF Token.';

            $this->saveToSession($errorMessages);

            return false;
        }

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

    protected function validateCsrf(array $data): bool
    {
        //todo: checking for field existence is stupid
        if (array_key_exists(CsrfValidator::TOKEN_KEY, $data)) {
            return CsrfValidator::getValidator()->isValid($data[CsrfValidator::TOKEN_KEY]);
        }

        return true;
    }
}
