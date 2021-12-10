<?php

namespace Fwt\Framework\Kernel\Validator\Rules;

interface RuleInterface
{
    public function validate($value): bool;

    public function getErrorMessage(): string;
}
