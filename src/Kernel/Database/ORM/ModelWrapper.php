<?php

namespace Fwt\Framework\Kernel\Database\ORM;

use Fwt\Framework\Kernel\Database\ORM\Models\AbstractModel;
use Fwt\Framework\Kernel\Exceptions\IllegalValueException;

class ModelWrapper extends AbstractModel
{
    public const STATE_INSERT = 'insert';
    public const STATE_UPDATE = 'update';
    public const STATE_DELETE = 'delete';
    public const STATE_IGNORE = 'ignore';
    private const STATES = [self::STATE_INSERT, self::STATE_UPDATE, self::STATE_DELETE, self::STATE_IGNORE];

    protected AbstractModel $model;
    private string $state;

    public function __construct(AbstractModel $model, string $state = null)
    {
        $this->model = $model;
        $this->updateFields();
        $this->setState($state ?? self::STATE_IGNORE);
    }

    public static function wrap(AbstractModel $model, string $state = null): self
    {
        return new self($model, $state);
    }

    public function initialize(): AbstractModel
    {
        $this->setState(self::STATE_IGNORE);

        return $this->model->initialize();
    }

    public function __set($name, $value): void
    {
        $this->model->__set($name, $value);
        $this->setState(self::STATE_UPDATE);
        $this->updateFields();
    }

    public function __get(string $name)
    {
        return $this->model->__get($name);
    }

    public function __isset($name): bool
    {
        return $this->model->__isset($name);
    }

    public function __unset($name): void
    {
        $this->model->__unset($name);
        $this->setState(self::STATE_UPDATE);
        $this->updateFields();
    }

    public function delete(): void
    {
        $this->model->delete();
        $this->setState(self::STATE_DELETE);
    }

    public function update(array $data): void
    {
        $this->model->update($data);
        $this->setState(self::STATE_IGNORE);
    }

    public function setState(string $state): self
    {
        IllegalValueException::checkValue($state, self::STATES);

        $this->state = $state;

        return $this;
    }

    protected function updateFields(): void
    {
        $this->fields = $this->model->fields;
        $this->relations = $this->model->relations;
    }

    public static function __set_state($fields): self
    {
        //todo: change exception
        throw new \Exception('Unavailable');
    }

    public static function where($where): ModelCollection
    {
        //todo: change exception
        throw new \Exception('Unavailable');
    }

    public static function all(): ModelCollection
    {
        //todo: change exception
        throw new \Exception('Unavailable');
    }

    public static function find($id): ?self
    {
        //todo: change exception
        throw new \Exception('Unavailable');
    }

    public static function create(array $data): AbstractModel
    {
        //todo: change exception
        throw new \Exception('Unavailable');
    }

    public static function createDry(array $data): AbstractModel
    {
        //todo: change exception
        throw new \Exception('Unavailable');
    }

    public static function getIdColumn(): string
    {
        //todo: change exception
        throw new \Exception('Unavailable');
    }

    public static function getTableName(): string
    {
        //todo: change exception
        throw new \Exception('Unavailable');
    }
}
