<?php

namespace Fwt\Framework\Kernel\Exceptions\Resolver;

use Throwable;
use ReflectionParameter;
use ReflectionMethod;
use ReflectionClass;

class UndefinedParameterException extends ObjectResolverException
{
    public function __construct(ReflectionParameter $parameter, ReflectionMethod $method, ReflectionClass $class, Throwable $previous = null)
    {
        $message = sprintf("Undefined parameter '%s' of type %s in %s::%s",
            $parameter->getName(), $parameter->getType(), $class->getName(), $method->getName()
        );

        parent::__construct($message, 500, $previous);
    }
}
