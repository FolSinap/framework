<?php

namespace Fwt\Framework\Kernel\Validator\Rules;

class EmailRule implements IRule
{
    protected string $errorMessage = 'Value must be correct email.';

    public function __construct(string $errorMessage = null)
    {
        if ($errorMessage) {
            $this->errorMessage = $errorMessage;
        }
    }

    public function validate($value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
