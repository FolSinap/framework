<?php

namespace FW\Kernel\Database\QueryBuilder\Where;

class OrExpression extends AndExpression
{
    public function build(): string
    {
        return self::OR . ' ' . $this->expression->build();
    }
}
