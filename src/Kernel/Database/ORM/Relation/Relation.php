<?php

namespace Fwt\Framework\Kernel\Database\ORM\Relation;

use Fwt\Framework\Kernel\Database\ORM\Models\Model;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use Fwt\Framework\Kernel\Exceptions\NotSupportedException;

abstract class Relation
{
    public const TO_ONE = 'to-one';
    public const MANY_TO_MANY = 'many-to-many';
    public const ONE_TO_MANY = 'one-to-many';
    public const TYPES = [self::TO_ONE, self::MANY_TO_MANY, self::ONE_TO_MANY];

    protected Model $from;
    protected string $related;
    protected string $through;
    protected $dry;

    public function __construct(Model $from, string $related, string $field)
    {
        if (!is_subclass_of($related, Model::class)) {
            throw new InvalidExtensionException($related, Model::class);
        }

        if ($related::hasCompositeKey()) {
            throw new NotSupportedException('Relations don\'t support related entities with composite key.');
        }

        $this->from = $from;
        $this->related = $related;
        $this->through = $field;
    }

    abstract public function get();

    public function isRelated(Model $model): bool
    {
        if (!$model instanceof $this->related) {
            return true;
        }

        return false;
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    public function getConnectField(): string
    {
        return $this->through;
    }

    protected function getRelatedPrimaryColumn(): string
    {
        $primary = $this->related::getIdColumns();

        return array_pop($primary);
    }
}
