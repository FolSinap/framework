<?php

namespace FW\Kernel\Database\ORM\Models;

use FW\Kernel\Database\ORM\Casting\Caster;
use FW\Kernel\Database\ORM\ModelCollection;
use FW\Kernel\Database\ORM\ModelRepository;
use FW\Kernel\Database\ORM\Relation\Relation;
use FW\Kernel\Database\ORM\Relation\ManyToManyRelation;
use FW\Kernel\Database\ORM\Relation\OneToManyRelation;
use FW\Kernel\Database\ORM\Relation\RelationFactory;
use FW\Kernel\Database\ORM\Relation\ToOneRelation;
use FW\Kernel\Database\ORM\UnitOfWork;
use FW\Kernel\Database\ORM\WhereBuilderFacade;
use FW\Kernel\Exceptions\IllegalTypeException;
use FW\Kernel\Exceptions\InvalidExtensionException;
use FW\Kernel\Exceptions\ORM\ModelInitializationException;
use FW\Kernel\Exceptions\ORM\PrimaryKeyException;
use FW\Kernel\Exceptions\ORM\RelationDefinitionException;
use LogicException;

abstract class Model
{
    public const RELATIONS = [];
    protected const ID_COLUMNS = ['id'];

    protected static array $tableNames;
    protected static array $columns = [];
    protected static array $loadedRelations = [];
    protected static array $casts = [];
    protected array $fields = [];
    protected array $changed = [];
    /** @var Relation[] $relations */
    private array $relations = [];
    private bool $exists = false;
    private bool $isChanged = false;
    private ?PrimaryKey $primary;
    private ?RelationFactory $relationFactory;
    private static ModelRepository $repository;

    public function __construct()
    {
        $this->relationFactory = $this->getFactory();
        $this->initIdColumns();
        $this->initRelations();
    }

    public static function getColumns(): array
    {
        if (empty(self::$columns) || !array_key_exists(static::class, self::$columns)) {
            self::$columns[static::class] = self::getRepository()->getTableScheme(static::class);
        }

        return self::$columns[static::class];
    }

    public static function find($id, array $relations = []): ?static
    {
        $id = new PrimaryKey(self::primaryKeyToAssoc($id));

        $model = self::getRepository()->find(static::class, $id, $relations);

        if ($model) {
            $model->setExists();
        }

        return $model;
    }

    public static function all(array $relations = []): ModelCollection
    {
        $models = self::getRepository()->allByClass(static::class, $relations);

        self::addLoadedRelations($relations);
        self::setExistsAll($models);

        return $models;
    }

    public static function fromId($id): static
    {
        $ids = self::primaryKeyToAssoc($id);

        $model = static::createDry($ids)->initIdColumns()->setExists()->setIsChanged();
        UnitOfWork::getInstance()->registerClean($model);

        return $model;
    }

    public static function fromIds(array $ids): ModelCollection
    {
        $collection = new ModelCollection();

        foreach ($ids as $id) {
            $collection[] = static::fromId($id);
        }

        return $collection;
    }

    public static function createDry(array $data): static
    {
        $model = new static();

        foreach ($data as $property => $value) {
            $model->silentSet($property, $value);
        }

        UnitOfWork::getInstance()->registerNew($model);

        return $model;
    }

    public static function create(array $data): static
    {
        $model = static::createDry($data);

        $model->insert();

        UnitOfWork::getInstance()->registerClean($model);

        return $model;
    }

    public static function deleteByIds(PrimaryKey|int|string|array ...$ids): void
    {
        $ids = array_map(function (PrimaryKey|int|string|array $id) {
            return match (true) {
                $id instanceof PrimaryKey => $id,
                is_int($id) || is_string($id) => new PrimaryKey([array_first(static::getIdColumns()) => $id]),
                is_array($id) => new PrimaryKey($id),
            };
        }, $ids);

        self::getRepository()->deleteManyById(static::class, ...$ids);
    }

    public function fetch(): static
    {
        if (!$this->primary()) {
            throw ModelInitializationException::idIsNotSet($this);
        }

        return static::find($this->primary());
    }

    public function synchronize(): static
    {
        if (!$this->exists()) {
            $this->insert();
        } elseif ($this->isChanged()) {
            $this->update();
        }

        return $this;
    }

    public function delete(): void
    {
        self::getRepository()->delete($this);

        $this->setExists(false);
    }

    public function update(array $data = []): void
    {
        self::getRepository()->update($this, $data);

        foreach ($this->relations as $name => $relation) {
            if ($relation instanceof ToOneRelation || !in_array($name, $this->changed)) {
                continue;
            }

            $relation->clear();
            $relation->addMany($this->fields[$name]);
        }

        $this->setExists()->setIsChanged(false);
    }

    public function setPrimary($id): static
    {
        $key = new PrimaryKey(self::primaryKeyToAssoc($id));

        foreach ($key->getValues() as $field => $value) {
            $this->$field = $value;
        }

        $this->primary = $key;

        return $this;
    }

    public function primary(): array
    {
        return $this->primary->getValues();
    }

    public function getPrimaryKey(): PrimaryKey
    {
        return $this->primary;
    }

    public static function getIdColumns(): array
    {
        return static::ID_COLUMNS;
    }

    public static function hasCompositeKey(): bool
    {
        return count(static::ID_COLUMNS) > 1;
    }

    public static function where(string $field, $value, string $expression = '='): WhereBuilderFacade
    {
        return self::getRepository()->where(static::class, $field, $value, $expression);
    }

    public static function whereIn(string $field, array $values): WhereBuilderFacade
    {
        return self::getRepository()->whereIn(static::class, $field, $values);
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

    public static function setTableName(string $table)
    {
        static::$tableNames[static::class] = $table;
    }

    public function prepareForExport()
    {
        $this->relations = [];
        $this->relationFactory = null;
//        $this->primary = null;
    }

    public function insert(): void
    {
        self::getRepository()->insert($this);

        foreach ($this->relations as $name => $relation) {
            if ($relation instanceof ToOneRelation) {
                continue;
            }

            if (!$this->fields[$name]->isEmpty()) {
                $relation->addMany($this->fields[$name]);
            }
        }

        $this->setExists()->setIsChanged(false)->initRelations();
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    /**
     * @return ToOneRelation|ManyToManyRelation|OneToManyRelation
     *
     * @throws RelationDefinitionException
     */
    public function getRelation(string $name): Relation
    {
        if (!$this->relationExists($name)) {
            throw RelationDefinitionException::undefinedRelation($this, $name);
        }

        return $this->relations[$name];
    }

    public function relationExists(string $name): bool
    {
        return array_key_exists($name, $this->relations);
    }

    public static function __set_state($fields): static
    {
        $model = new static();

        $model->fields = $fields['fields'];
        $model->changed = $fields['changed'];
        $model->relations = $fields['relations'];
        $model->exists = $fields['exists'];
        $model->isChanged = $fields['isChanged'];
        $model->primary = $fields['primary'];
        $model->relationFactory = $fields['relationFactory'];

        return $model;
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->relations) && !in_array($name, static::loadedRelations())) {
            $relation = $this->relations[$name]->get();
            $this->silentSet($name, $relation);
            static::addLoadedRelations([$name]);
        }

        return $this->fields[$name] ?? null;
    }

    public function getLazy(string $name)
    {
        if (!array_key_exists($name, $this->fields) && array_key_exists($name, $this->relations)) {
            $relation = $this->relations[$name]->getDry();
            $this->silentSet($name, $relation);
        }

        return $this->fields[$name] ?? null;
    }

    public function __set(string $name, $value): void
    {
        $fields = array_keys($this->fields);

        $isChanged = $this->setFieldValue($name, $value);

        if (in_array($name, $fields)) {
            $this->changed[] = $name;
        }

        if ($isChanged) {
            UnitOfWork::getInstance()->registerDirty($this);
        }

        $this->setIsChanged($isChanged);
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
        $fields = [];

        foreach (static::getColumns() as $column) {
            if ($this->$column !== null) {
                $fields[$column] = $this->$column;
            }
        }

        if (empty($fields)) {
            return array_diff_key($this->fields, $this->relations);
        }

        return $fields;
    }

    public function isSynchronized(): bool
    {
        return $this->exists && !$this->isChanged;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function isChanged(): bool
    {
        return $this->isChanged;
    }

    private function setExists(bool $exists = true): static
    {
        if (!$exists) {
            $this->isChanged = false;
        }

        $this->exists = $exists;

        return $this;
    }

    private static function setExistsAll(ModelCollection $models, bool $exists = true): void
    {
        foreach ($models as $model) {
            if (!$model instanceof self) {
                throw new InvalidExtensionException($model, self::class);
            }

            $model->setExists($exists);
        }
    }

    private function setIsChanged(bool $isChanged = true): static
    {
        if (!$this->exists && $isChanged) {
            throw new LogicException('Couldn\'t set isChanged = true, while model does not exist.');
        }

        $this->isChanged = $isChanged;

        return $this;
    }

    private function initRelations(): void
    {
        foreach (static::RELATIONS as $field => $definition) {
            if (isset($this->$field)) {
                continue;
            }

            $relation = $this->getFactory()->create($this, $definition);

            $this->$field = $relation->getDry();
            $this->relations[$field] = $relation;
        }
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
            self::$repository = new ModelRepository();
        }

        return self::$repository;
    }

    private static function primaryKeyToAssoc($key): array
    {
        switch (gettype($key)) {
            case 'array':
                if (!self::hasCompositeKey() && count($key) > 1) {
                    throw PrimaryKeyException::compositeValueForNonCompositeKey();
                }

                if (empty($key)) {
                    throw PrimaryKeyException::emptyArray();
                }

                $keys = $key;

                break;
            case 'integer':
            case 'string':
                if (static::hasCompositeKey()) {
                    throw PrimaryKeyException::singleValueForCompositeKey();
                }

                $keys[self::ID_COLUMNS[0]] = $key;

                break;
            case 'object':
                if ($key instanceof PrimaryKey) {
                    $keys = $key->getValues();

                    break;
                }
            default:
                throw new IllegalTypeException($key, ['array', 'string', 'integer', PrimaryKey::class]);
        }

        return $keys;
    }

    private function setFieldValue(string $name, mixed $value): bool
    {
        $isChanged = $this->exists();

        if ($this->relationExists($name)) {
            $isChanged = $this->setRelation($name, $value);
        } else {
            if (array_key_exists($name, static::$casts) && !is_null($value)) {
                $value = (new Caster())->cast($value, static::$casts[$name]);
            }

            $this->fields[$name] = $value;
        }

        if (isset($this->primary) && in_array($name, static::ID_COLUMNS)) {
            $this->primary->setValue($name, $value);
        }

        return $isChanged;
    }

    private function silentSet(string $name, $value): void
    {
        $this->setFieldValue($name, $value);
    }

    /**
     * @param string $name Name of a relation
     * @param $value
     * @return bool new value for $this->isChanged
     */
    private function setRelation(string $name, $value): bool
    {
        $relation = $this->relations[$name];
        $isChanged = $this->isChanged();

        if ($relation instanceof ToOneRelation) {
            $original = $this->{$relation->getConnectField()};

            $this->setToOneRelation($relation, $value);

            if ($this->{$relation->getConnectField()} !== $original) {
                $isChanged = $this->exists();
            }
        } else {
            $value = $this->normalizeToManyValue($value);
        }

        $this->fields[$name] = $value;

        return $isChanged;
    }

    private function normalizeToManyValue($value)
    {
        switch (true) {
            case is_array($value):
                $value = new ModelCollection($value);

                break;
            case ($value instanceof ModelCollection):
                break;
            case  is_null($value):
                $value = new ModelCollection();

                break;
            default:
                throw new IllegalTypeException($value, ['null', 'array', ModelCollection::class]);
        }

        return $value;
    }

    private function setToOneRelation(ToOneRelation $relation, $value)
    {
        $relatedClass = $relation->getRelated();

        if (is_null($value)) {
            $this->{$relation->getConnectField()} = $value;
        } else {
            if (!$value instanceof $relatedClass) {
                throw new IllegalTypeException($value, [$relatedClass]);
            }

            $primary = $value->primary();

            $this->{$relation->getConnectField()} = array_pop($primary);
        }
    }

    private static function addLoadedRelations(array $relations): void
    {
        if (array_key_exists(static::class, self::$loadedRelations)) {
            self::$loadedRelations[static::class] = array_merge(self::$loadedRelations[static::class], $relations);
        } else {
            self::$loadedRelations[static::class] = $relations;
        }
    }

    private static function loadedRelations(): array
    {
        if (!array_key_exists(static::class, self::$loadedRelations)) {
            self::$loadedRelations[static::class] = [];
        }

        return self::$loadedRelations[static::class];
    }

    private function initIdColumns(): static
    {
        if (empty(static::ID_COLUMNS)) {
            throw new LogicException('Model must have at least one primary key column');
        }

        $ids = [];

        foreach (static::ID_COLUMNS as $column) {
            $ids[$column] = $this->fields[$column] ?? null;
        }

        $this->primary = new PrimaryKey($ids);

        return $this;
    }
}
