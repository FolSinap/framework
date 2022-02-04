<?php

namespace App\Controllers\RequestValidators\Books;

use FW\Kernel\Validator\RequestValidator;
use FW\Kernel\Validator\Rules\TypeRule;

class CreateRequestValidator extends RequestValidator
{
    public function getRules(): array
    {
        return [
            'title' => [new TypeRule(TypeRule::TYPE_STRING)],
            'genres' => [new TypeRule([TypeRule::TYPE_ARRAY, TypeRule::TYPE_NULL])],
        ];
    }
}
