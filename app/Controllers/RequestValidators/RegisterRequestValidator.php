<?php

namespace App\Controllers\RequestValidators;

use Fwt\Framework\Kernel\Validator\RequestValidator;
use Fwt\Framework\Kernel\Validator\Rules\TypeRule;

class RegisterRequestValidator extends RequestValidator
{
    public function getRules(): array
    {
        return [
            'email' => [new TypeRule(TypeRule::TYPE_STRING)],
            'password' => [new TypeRule(TypeRule::TYPE_STRING)],
        ];
    }
}
