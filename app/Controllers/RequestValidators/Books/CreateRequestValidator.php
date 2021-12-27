<?php

namespace App\Controllers\RequestValidators\Books;

use Fwt\Framework\Kernel\Validator\RequestValidator;
use Fwt\Framework\Kernel\Validator\Rules\TypeRule;

class CreateRequestValidator extends RequestValidator
{
    public function getRules(): array
    {
        return [
            'title' => [new TypeRule(TypeRule::TYPE_STRING)]
        ];
    }
}
