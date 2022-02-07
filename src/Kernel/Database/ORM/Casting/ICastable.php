<?php

namespace FW\Kernel\Database\ORM\Casting;

interface ICastable
{
    public static function cast(string $value): self;
}
