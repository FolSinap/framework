<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

class Expression implements IExpressionBuilder
{
    protected string $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    public function build(): string
    {
        return $this->expression;
    }
}
