<?php

namespace Fwt\Framework\Kernel\Database\ORM\Relation;

use Fwt\Framework\Kernel\Database\ORM\Models\Model;
use Fwt\Framework\Kernel\Exceptions\ORM\RelationDefinitionException;

class RelationFactory
{
    public function create(Model $model, array $config): AbstractRelation
    {
        RelationDefinitionException::checkRequiredKeys(['class', 'field'], $config);

        switch ($config['type'] ?? AbstractRelation::TO_ONE) {
            case AbstractRelation::MANY_TO_MANY:
                RelationDefinitionException::checkRequiredKeys(['defined_by'], $config);

                return new ManyToManyRelation(
                    $model,
                    $config['class'],
                    $config['field'],
                    $config['defined_by'],
                    $config['pivot'] ?? null
                );
            case AbstractRelation::ONE_TO_MANY:
                return new OneToManyRelation($model, $config['class'], $config['field']);
            default:
                return new ToOneRelation($model, $config['class'], $config['field']);
        }
    }
}
