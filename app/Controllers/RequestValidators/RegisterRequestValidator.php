<?php

namespace App\Controllers\RequestValidators;

use FW\Kernel\Validator\RequestValidator;
use FW\Kernel\Validator\Rules\EmailRule;
use FW\Kernel\Validator\Rules\TypeRule;
use FW\Kernel\Validator\Rules\UniqueRule;

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
