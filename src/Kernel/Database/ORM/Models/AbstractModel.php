<?php

namespace Fwt\Framework\Kernel\Database\ORM\Models;

use Fwt\Framework\Kernel\App;
use Fwt\Framework\Kernel\Database\Database;
use Fwt\Framework\Kernel\Database\ORM\ModelCollection;
use Fwt\Framework\Kernel\Database\ORM\ModelRepository;
use Fwt\Framework\Kernel\Database\ORM\Relation\AbstractRelation;
use Fwt\Framework\Kernel\Database\ORM\Relation\RelationFactory;
use Fwt\Framework\Kernel\Database\ORM\Relation\ToOneRelation;
use Fwt\Framework\Kernel\Database\ORM\WhereBuilderFacade;
use Fwt\Framework\Kernel\Exceptions\IllegalTypeException;
use Fwt\Framework\Kernel\Exceptions\InvalidExtensionException;
use Fwt\Framework\Kernel\Exceptions\ORM\RelationDefinitionException;

abstract class AbstractModel
{
    protected const RELATIONS = [];

    protected static array $tableNames;
    protected static array $columns = [];
    protected array $fields = [];
    /** @var AbstractRelation[] $relations */
    private array $relations = [];
    private bool $exists = false;
    private bool $isChanged = false;
    private ?RelationFactory $relationFactory;
    private static ModelRepository $repository;

    public function __construct()
    {
        $this->relationFactory = $this->getFactory();
        $this->initRelations();
    }

    public static function find($id): ?self
    {
        $model = self::getRepository()->find(static::class, $id);

        if ($model) {
            $model->setExists();
        }

        return $model;
    }

    public static function all(): ModelCollection
    {
        $models = self::getRepository()->allByClass(static::class);

        self::setExistsAll($models);

        return $models;
    }

    public static function fromId($id): self
    {
        return static::createDry([static::getIdColumn() => $id])->setExists()->setIsChanged();
    }

    public static function createDry(array $data): self
    {
        $model = new static();

        foreach ($data as $property => $value) {
            $model->$property = $value;
        }

        return $model;
    }

    public static function create(array $data): self
    {
        $object = static::createDry($data);

        $object->insert();

        return $object;
    }

    public function fetch(): self
    {
        if (!$this->primary()) {
            //todo: change exception
            throw new \Exception('id is not set');
        }

        return static::find($this->primary());
    }

    public function synchronize(): self
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

        $this->setExists()->setIsChanged(false);
    }

    public function primary()
    {
        //todo: add cases where primary key includes multiple cols

        return $this->{$this::getIdColumn()};
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

    public function prepareForExport()
    {
        $this->relations = [];
        $this->relationFactory = null;
    }

    public function insert(): void
    {
        self::getRepository()->insert($this);

        $this->setExists()->setIsChanged(false)->initRelations();
    }

    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getRelation(string $name): AbstractRelation
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
        $isChanged = $this->exists();

        if (array_key_exists($name, $this->relations)) {
            $relation = $this->relations[$name];
            $relatedClass = $relation->getRelated();
            $isChanged = $this->isChanged();

            if ($relation instanceof ToOneRelation) {
                $original = $this->{$relation->getConnectField()};

                if (is_null($value)) {
                    $this->{$relation->getConnectField()} = $value;
                } else {
                    if (!$value instanceof $relatedClass) {
                        throw new IllegalTypeException($value, [$relatedClass]);
                    }

                    $this->{$relation->getConnectField()} = $value->{$value::getIdColumn()};
                }

                if ($this->{$relation->getConnectField()} !== $original) {
                    $isChanged = $this->exists();
                }
            }
        }

        $this->fields[$name] = $value;
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

        foreach (static::$columns as $column) {
            $fields[$column] = $this->$column;
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

    private function setExists(bool $exists = true): self
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

    private function setIsChanged(bool $isChanged = true): self
    {
        if (!$this->exists && $isChanged) {
            //todo: change exception
            throw new \Exception('Couldn\'t set isChanged = true, while model is not in database');
        }

        $this->isChanged = $isChanged;

        return $this;
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
            self::$repository = new ModelRepository();
        }

        return self::$repository;
    }
}
