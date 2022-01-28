<?php

namespace Fwt\Framework\Kernel\Exceptions\ORM;

use Fwt\Framework\Kernel\Database\ORM\Models\Model;
use LogicException;

class RelationDefinitionException extends LogicException
{
    public static function checkRequiredKeys(array $keys, array $data): void
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                throw new self("Required key $key is not defined in relation definition");
            }
        }
    }

    public static function undefinedRelation(Model $model, string $relation): self
    {
        throw new self("Undefined relation $relation for" . get_class($model));
    }
}
