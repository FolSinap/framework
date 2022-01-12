<?php

namespace Fwt\Framework\Kernel\Database\ORM;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Database\ORM\Models\AnonymousModel;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;

class Relation
{
    public const TO_MANY = 'MANY';
    public const TO_ONE = 'ONE';
    protected const TYPES = [self::TO_MANY, self::TO_ONE];

    protected AbstractModel $from;
    protected string $related;
    protected string $through;
    protected string $type;
    protected string $pivot;
    protected string $definedBy;
    /** @var AbstractModel|ModelCollection|null $dry */
    protected $dry;

    public function __construct(AbstractModel $from, string $related, string $field, string $type = null)
    {
        if (!is_subclass_of($related, AbstractModel::class)) {
            throw new InvalidExtensionException($related, AbstractModel::class);
        }

        $this->from = $from;
        $this->related = $related;
        $this->through = $field;

        $type = $type ?? self::TO_ONE;
        IllegalValueException::checkValue($type, self::TYPES);
        $this->type = $type;
    }

    public function get()
    {
        $dry = $this->getDry();

        if ($dry instanceof ModelCollection) {
            return $dry->initializeAll();
        }

        return $dry ? $dry->initialize() : null;
    }

    public function getDry()
    {
        if (!isset($this->dry)) {
            if ($this->type === self::TO_MANY) {
                if (!isset($this->pivot)) {
                    $tables = [$this->from::getTableName(), $this->related::getTableName()];

                    sort($tables);

                    $this->pivot = implode('_', $tables);
                }

                AnonymousModel::$tableNames[AnonymousModel::class] = $this->pivot;

                $id = $this->from->{$this->from::getIdColumn()};

                if (is_null($id)) {
                    $this->dry = [];

                    return $this->dry;
                }

                $pivots = AnonymousModel::where([$this->definedBy => $id]);

                foreach ($pivots as $key => $pivot) {
                    $pivots[$key] = $this->related::createDry([$this->related::getIdColumn() => $pivot->{$this->through}]);
                }

                $this->dry = new ModelCollection($pivots);
            } else {
                $foreignKey = $this->from->{$this->through};

                if (is_null($foreignKey)) {
                    $this->dry = null;
                } else {
                    $this->dry = $this->related::createDry([$this->related::getIdColumn() => $foreignKey]);
                }
            }
        }

        return $this->dry;
    }

    public function getRelated(): string
    {
        return $this->related;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getConnectField(): string
    {
        return $this->through;
    }

    public function setPivotTable(string $table): self
    {
        $this->pivot = $table;

        return $this;
    }

    public function setDefinedBy(string $column): self
    {
        $this->definedBy = $column;

        return $this;
    }
}
