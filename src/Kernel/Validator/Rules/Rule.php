<?php

namespace Fwt\Framework\Kernel\Validator\Rules;

interface Rule
{
    public function validate($value): bool;

    public function getErrorMessage(): string;
}
