<?php

namespace Fwt\Framework\Kernel\Database\ORM\Relation;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Database\ORM\Models\AnonymousModel;

class ManyToManyRelation extends OneToManyRelation
{
    protected string $pivot;
    protected string $definedBy;

    public function __construct(AbstractModel $from, string $related, string $field, string $definedBy, string $pivot = null)
    {
        parent::__construct($from, $related, $field);

        $this->pivot = $pivot ?? $this->defaultPivot();
        $this->definedBy = $definedBy;
    }

    public function getDry()
    {
        if (!isset($this->dry)) {
            AnonymousModel::$tableNames[AnonymousModel::class] = $this->pivot;

            $id = $this->from->{$this->from::getIdColumn()};

            if (is_null($id)) {
                $this->dry = [];

                return $this->dry;
            }

            $pivots = AnonymousModel::where($this->definedBy, $id)->fetch();

            foreach ($pivots as $key => $pivot) {
                $pivots[$key] = $this->related::createDry([$this->related::getIdColumn() => $pivot->{$this->through}]);
            }

            $this->dry = $pivots;
        }

        return $this->dry;
    }

    protected function defaultPivot(): string
    {
        $tables = [$this->from::getTableName(), $this->related::getTableName()];

        sort($tables);

        return implode('_', $tables);
    }
}