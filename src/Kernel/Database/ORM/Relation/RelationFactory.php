<?php

namespace FW\Kernel\Database\ORM\Relation;

use FW\Kernel\Database\ORM\Models\Model;
use FW\Kernel\Exceptions\ORM\RelationDefinitionException;

class RelationFactory
{
    public function create(Model $model, array $config): Relation
    {
        RelationDefinitionException::checkRequiredKeys(['class', 'field'], $config);

        switch ($config['type'] ?? Relation::TO_ONE) {
            case Relation::MANY_TO_MANY:
                RelationDefinitionException::checkRequiredKeys(['defined_by'], $config);

                return new ManyToManyRelation(
                    $model,
                    $config['class'],
                    $config['field'],
                    $config['defined_by'],
                    $config['pivot'] ?? null
                );
            case Relation::ONE_TO_MANY:
                return new OneToManyRelation($model, $config['class'], $config['field']);
            default:
                return new ToOneRelation($model, $config['class'], $config['field']);
        }
    }
}
