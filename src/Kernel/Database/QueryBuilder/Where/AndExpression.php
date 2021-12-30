<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

class AndExpression extends Expression
{
    public function build(): string
    {
        return self::AND . ' ' . parent::build();
    }
}
