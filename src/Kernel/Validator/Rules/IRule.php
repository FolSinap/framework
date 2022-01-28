<?php

namespace Fwt\Framework\Kernel\Validator\Rules;

interface IRule
{
    public function validate($value): bool;

    public function getErrorMessage(): string;
}
