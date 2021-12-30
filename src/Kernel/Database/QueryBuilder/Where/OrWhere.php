<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

class OrWhere extends Where
{
    public function build(): string
    {
        return self::OR . ' ' . parent::build();
    }
}
