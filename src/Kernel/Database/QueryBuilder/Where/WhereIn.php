<?php

namespace FW\Kernel\Database\QueryBuilder\Where;

class WhereIn extends Where
{
    protected const WHERE_EXPRESSIONS = ['IN'];

    public function __construct(string $field, array $value)
    {
        parent::__construct($field, '(' . implode(',', $value) . ')', 'IN');
    }
}
