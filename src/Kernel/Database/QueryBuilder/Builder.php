<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

interface Builder
{
    public function getQuery(): string;
}
