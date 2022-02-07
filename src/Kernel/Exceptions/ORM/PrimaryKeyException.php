<?php

namespace FW\Kernel\Exceptions\ORM;

use LogicException;

class PrimaryKeyException extends LogicException
{
    public static function singleValueForCompositeKey(): self
    {
        return new self('Cannot use single value for composite key.');
    }

    public static function compositeValueForNonCompositeKey(): self
    {
        return new self('Cannot use composite value for non composite key.');
    }

    public static function emptyArray(): self
    {
        return new self('Cannot use empty array as primary key');
    }
}
