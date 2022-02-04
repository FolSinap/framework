<?php

namespace FW\Kernel\Exceptions\ORM;

use DomainException;
use FW\Kernel\Database\ORM\Models\Model;

class UndefinedRelationException extends DomainException
{
    public function __construct(Model $from, Model $related)
    {
        parent::__construct(get_class($related) . ' is not related to ' . get_class($from));
    }
}
