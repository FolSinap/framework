<?php

namespace Fwt\Framework\Kernel\Exceptions\ORM;

use Fwt\Framework\Kernel\Database\ORM\Models\Model;
use LogicException;
use Throwable;

class ModelInitializationException extends LogicException
{
    public function __construct(string $message, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Initialization Error: $message", $code, $previous);
    }

    public static function idIsNotSet(Model $model): self
    {
        return new self('Cannot initialize model ' . get_class($model) . ' when id is not set.');
    }

    public static function nonexistentModel(Model $model): self
    {
        return new self('Model doesn\'t exist in DB ' . get_class($model));
    }
}
