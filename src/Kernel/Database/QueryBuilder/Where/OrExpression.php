<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

class OrExpression extends AndExpression
{
    public function build(): string
    {
        return self::OR . ' ' . $this->expression->build();
    }
}
