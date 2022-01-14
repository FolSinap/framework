<?php

namespace Fwt\Framework\Kernel\Database\ORM\Models;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\ORM\ModelCollection;
use Fwt\Framework\Kernel\Database\ORM\ModelRepository;
use Fwt\Framework\Kernel\Database\ORM\Relation;
use Fwt\Framework\Kernel\Database\ORM\Relation\RelationFactory;
use Fwt\Framework\Kernel\Database\ORM\WhereBuilderFacade;
use Fwt\Framework\Kernel\Database\QueryBuilder\Where\WhereBuilder;
use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;

abstract class AbstractModel
{
    protected const RELATIONS = [];

    protected static array $tableNames;
    protected array $fields = [];
    /** @var Relation[] $relations */
    private array $relations = [];
    private bool $isInitialized = false;
    private ?RelationFactory $relationFactory;
    private static ModelRepository $repository;

    public function __construct()
    {
        $this->relationFactory = $this->getFactory();
        $this->initRelations();
    }

    public static function find($id): ?self
    {
        $model = self::getRepository()->initializedById(static::class, $id);

        if ($model) {
            $model->setInitialized();
        }

        return $model;
    }

    public static function all(): ModelCollection
    {
        $models = self::getRepository()->allByClass(static::class);

        self::setInitializedAll($models);

        return $models;
    }

    public static function createDry(array $data): self
    {
        return self::getRepository()->dry(static::class, $data);
    }

    public static function create(array $data): self
    {
        $object = static::createDry($data);

        $object->insert();

        return $object;
    }

    public function initialize(): bool
    {
        if (!is_null(self::getRepository()->initialize($this))) {
            $this->setInitialized();
        }

        $this->initRelations();

        return $this->isInitialized();
    }

    public function delete(): void
    {
        self::getRepository()->delete($this);

        $this->setInitialized(false);
    }

    public function update(array $data): void
    {
        self::getRepository()->update($this, $data);
    }

    public static function getIdColumn(): string
    {
        //todo: add cases where primary key includes multiple cols

        return 'id';
    }

    public static function where(string $field, $value, string $expression = '='): WhereBuilderFacade
    {
        return self::getRepository()->where(static::class, $field, $value, $expression);
    }

    public static function getTableName(): string
    {
        if (!isset(static::$tableNames) || !array_key_exists(static::class, static::$tableNames)) {
            $explode = explode('\\', static::class);
            $single = strtolower(array_pop($explode));

            $lastLetter = substr($single, -1);
            $lastTwoLetter = substr($single, -2);

            if (in_array($lastLetter, ['x', 's']) || in_array($lastTwoLetter, ['sh', 'ch'])) {
                static::$tableNames[static::class] = $single . 'es';
            } elseif ($lastLetter === 'y') {
                static::$tableNames[static::class] = rtrim($single, 'y') . 'ies';
            } else {
                static::$tableNames[static::class] = $single . 's';
            }
        }

        return static::$tableNames[static::class];
    }

    public function prepareForExport()
    {
        $this->relations = [];
        $this->relationFactory = null;
    }

    public function insert(): void
    {
        self::getRepository()->insert($this);

        $this->setInitialized()->initRelations();
    }

    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    public static function __set_state($fields): self
    {
        return static::createDry($fields);
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->relations)) {
            $relation = $this->relations[$name]->get();
            $this->$name = $relation;
        }

        return $this->fields[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        if (array_key_exists($name, $this->relations)) {
            $relation = $this->relations[$name];
            $relatedClass = $relation->getRelated();

            if ($relation instanceof Relation\ToOneRelation) {
                if (is_null($value)) {
                    $this->{$relation->getConnectField()} = $value;
                } else {
                    if (!$value instanceof $relatedClass) {
                        throw new IllegalTypeException($value, [$relatedClass]);
                    }

                    $this->{$relation->getConnectField()} = $value->{$value::getIdColumn()};
                }
            }
        }

        $this->fields[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    public function __unset(string $name): void
    {
        unset($this->fields[$name]);
    }

    public function getForInsertion(): array
    {
        return array_diff_key($this->fields, $this->relations);
    }

    protected function setInitialized(bool $isInitialized = true): self
    {
        $this->isInitialized = $isInitialized;

        return $this;
    }

    protected static function setInitializedAll(ModelCollection $models, bool $isInitialized = true): void
    {
        foreach ($models as $model) {
            if (!$model instanceof self) {
                throw new InvalidExtensionException($model, self::class);
            }

            $model->setInitialized($isInitialized);
        }
    }

    protected static function getDatabase(): Database
    {
        return App::$app->getContainer()->get(Database::class);
    }

    private function initRelations(): self
    {
        foreach (static::RELATIONS as $field => $definition) {
            if (isset($this->$field)) {
                continue;
            }

            $relation = $this->getFactory()->create($this, $definition);

            $this->$field = $relation->getDry();
            $this->relations[$field] = $relation;
        }

        return $this;
    }

    private function getFactory(): RelationFactory
    {
        if (!isset($this->relationFactory)) {
            $this->relationFactory = new RelationFactory();
        }

        return $this->relationFactory;
    }

    private static function getRepository(): ModelRepository
    {
        if (!isset(self::$repository)) {
            self::$repository = ModelRepository::getInstance();
        }

        return self::$repository;
    }
}
