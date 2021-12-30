<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

class OrExpression extends Expression
{
    public function build(): string
    {
        return self::OR . ' ' . parent::build();
    }
}
