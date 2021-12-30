<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

interface ExpressionBuilder
{
    public const OR = 'OR';
    public const AND = 'AND';

    public function build(): string;
}
