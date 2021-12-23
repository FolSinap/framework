<?php

namespace App\Controllers\RequestValidators;

use Fwt\Framework\Kernel\Validator\RequestValidator;
use Fwt\Framework\Kernel\Validator\Rules\EmailRule;
use Fwt\Framework\Kernel\Validator\Rules\TypeRule;
use Fwt\Framework\Kernel\Validator\Rules\UniqueRule;

class RegisterRequestValidator extends RequestValidator
{
    public function getRules(): array
    {
        return [
            'email' => [
                new TypeRule(TypeRule::TYPE_STRING),
                new EmailRule(),
                new UniqueRule('email', 'users'),
            ],
            'password' => [
                new TypeRule(TypeRule::TYPE_STRING),
            ],
        ];
    }
}
