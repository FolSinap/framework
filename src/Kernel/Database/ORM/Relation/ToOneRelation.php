<?php

namespace Fwt\Framework\Kernel\Database\ORM\Relation;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;

class ToOneRelation extends AbstractRelation
{
    public function get(): ?AbstractModel
    {
        $dry = $this->getDry();

        if ($dry && $dry->initialize()) {
            return $dry;
        }

        return null;
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
