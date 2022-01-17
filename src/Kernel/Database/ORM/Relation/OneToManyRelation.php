<?php

namespace Fwt\Framework\Kernel\Database\ORM\Relation;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;

class OneToManyRelation extends AbstractRelation
{
    public function __construct(AbstractModel $from, string $related, string $field)
    {
        parent::__construct($from, $related, $field);
    }

    public function get()
    {
        return $this->getDry()->initializeAll();
    }

    public function getDry()
    {
        if (!isset($this->dry)) {
            $id = $this->from->{$this->from::getIdColumn()};

            if (is_null($id)) {
                $this->dry = [];

                return $this->dry;
            }

            $this->dry = $this->related::where($this->through, $id)->fetch();
        }

        return $this->dry;
    }
}
