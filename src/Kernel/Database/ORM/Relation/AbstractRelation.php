<?php

namespace Fwt\Framework\Kernel\Database\ORM\Relation;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;

abstract class AbstractRelation
{
    public const TO_ONE = 'to-one';
    public const MANY_TO_MANY = 'many-to-many';
    public const ONE_TO_MANY = 'one-to-many';
    public const TYPES = [self::TO_ONE, self::MANY_TO_MANY, self::ONE_TO_MANY];

    protected AbstractModel $from;
    protected string $related;
    protected string $through;
    protected $dry;

    public function __construct(AbstractModel $from, string $related, string $field)
    {
        if (!is_subclass_of($related, AbstractModel::class)) {
            throw new InvalidExtensionException($related, AbstractModel::class);
        }

        $this->from = $from;
        $this->related = $related;
        $this->through = $field;
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    public function getConnectField(): string
    {
        return $this->through;
    }

    abstract public function getDry();

    abstract public function get();
}
