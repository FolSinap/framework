<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder\Where;

class AndWhere extends Where
{
    public function build(): string
    {
        return self::AND . ' ' . parent::build();
    }
}
