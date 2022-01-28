<?php

namespace Fwt\Framework\Kernel\Database\QueryBuilder;

interface IBuilder
{
    public function getQuery(): string;
}
