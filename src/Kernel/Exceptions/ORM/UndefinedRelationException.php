<?php

namespace Fwt\Framework\Kernel\Exceptions\ORM;

use DomainException;
use Fwt\Framework\Kernel\Database\ORM\Models\Model;

class UndefinedRelationException extends DomainException
{
    public function __construct(Model $from, Model $related)
    {
        parent::__construct(get_class($related) . ' is not related to ' . get_class($from));
    }
}
