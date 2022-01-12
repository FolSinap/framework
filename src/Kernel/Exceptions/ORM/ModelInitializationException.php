<?php

namespace Fwt\Framework\Kernel\Exceptions\ORM;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use LogicException;
use Throwable;

class ModelInitializationException extends LogicException
{
    public function __construct(string $message, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Initialization Error: $message", $code, $previous);
    }

    public static function idIsNotSet(AbstractModel $model): self
    {
        return new self('Cannot initialize model ' . get_class($model) . ' when id is not set.');
    }

    public static function updatingNotInitializedModel(AbstractModel $model): self
    {
        return new self('Cannot update not initialized model ' . get_class($model));
    }
}
