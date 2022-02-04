<?php

namespace FW\Kernel\Database\QueryBuilder;

interface IBuilder
{
    public function getQuery(): string;
}
