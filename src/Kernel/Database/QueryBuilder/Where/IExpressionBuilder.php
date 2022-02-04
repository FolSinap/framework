<?php

namespace FW\Kernel\Database\QueryBuilder\Where;

interface IExpressionBuilder
{
    public const OR = 'OR';
    public const AND = 'AND';

    public function build(): string;
}
