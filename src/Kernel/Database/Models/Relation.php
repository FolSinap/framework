<?php

namespace Fwt\Framework\Kernel\Database\Models;

use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;

class Relation
{
    //todo:: add different relation types
    protected const TYPES = [];

    protected AbstractModel $from;
    protected string $related;
    protected string $through;
    protected ?AbstractModel $dry;

    public function __construct(AbstractModel $from, string $related, string $field)
    {
        if (!is_subclass_of($related, AbstractModel::class)) {
            throw new InvalidExtensionException($related, AbstractModel::class);
        }

        $this->from = $from;
        $this->related = $related;
        $this->through = $field;
    }

    public function get(): ?AbstractModel
    {
        return $this->getDry() ? $this->getDry()->initialize() : null;
    }

    public function getDry(): ?AbstractModel
    {
        if (!isset($this->dry)) {
            $foreignKey = $this->from->{$this->through};

            if (is_null($foreignKey)) {
                $this->dry = null;
            } else {
                $this->dry = $this->related::createDry([$this->related::getIdColumn() => $foreignKey]);
            }
        }

        return $this->dry;
    }
}
