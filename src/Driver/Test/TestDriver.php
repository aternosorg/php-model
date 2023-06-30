<?php

namespace Aternos\Model\Driver\Test;

use Aternos\Model\Driver\Driver;
use Aternos\Model\Driver\Features\CRUDAbleInterface;
use Aternos\Model\Driver\Features\CRUDQueryableInterface;
use Aternos\Model\ModelInterface;
use Aternos\Model\ModelRegistry;
use Aternos\Model\Query\Query;
use Aternos\Model\Query\QueryResult;
use Exception;

class TestDriver extends Driver implements CRUDAbleInterface, CRUDQueryableInterface
{
    public const ID = "test";
    protected string $id = self::ID;

    /**
     * @var TestTable[]
     */
    protected array $tables = [];

    /**
     * @param array $tables
     * @return $this
     */
    public function addTables(array $tables): static
    {
        foreach ($tables as $name => $entries) {
            $this->addTable($name, $entries);
        }
        return $this;
    }

    /**
     * @param string $name
     * @param array $entries
     * @return $this
     */
    public function addTable(string $name, array $entries): static
    {
        $this->tables[$name] = new TestTable($name);
        $this->tables[$name]->addArrayEntries($entries);
        return $this;
    }

    /**
     * @param string $name
     * @return TestTable
     */
    public function getTable(string $name): TestTable
    {
        if (!isset($this->tables[$name])) {
            $this->tables[$name] = new TestTable($name);
        }
        return $this->tables[$name];
    }

    /**
     * @param string $tableName
     * @param array $entry
     * @return $this
     */
    public function addEntry(string $tableName, array $entry): static
    {
        $table = $this->getTable($tableName);
        $table->addEntry(new TestTableEntry($entry));
        return $this;
    }

    /**
     * @return $this
     */
    public function clearTables(): static
    {
        ModelRegistry::getInstance()->clearAll();
        $this->tables = [];
        return $this;
    }

    /**
     * @param string $tableName
     * @return $this
     */
    public function clearEntries(string $tableName): static
    {
        if (isset($this->tables[$tableName])) {
            $this->tables[$tableName]->clear();
        }
        return $this;
    }

    /**
     * @param ModelInterface $model
     * @return bool
     * @throws Exception
     */
    public function delete(ModelInterface $model): bool
    {
        $table = $this->getTable($model::getName());
        $entry = $table->getById($model->getId(), $model::getIdField());
        if (!$entry) {
            return false;
        }
        $table->deleteEntry($entry);
        return true;
    }

    /**
     * @param class-string<ModelInterface> $modelClass
     * @param mixed $id
     * @param ModelInterface|null $model
     * @return ModelInterface|null
     * @throws Exception
     */
    public function get(string $modelClass, mixed $id, ?ModelInterface $model = null): ?ModelInterface
    {
        $entry = $this->getTable($modelClass::getName())->getById($id, $modelClass::getIdField());
        if (!$entry) {
            return null;
        }
        if ($model) {
            return $entry->applyToModel($model);
        }
        return $modelClass::getModelFromData($entry->getData());
    }

    /**
     * @param ModelInterface $model
     * @return bool
     * @throws Exception
     */
    public function save(ModelInterface $model): bool
    {
        $table = $this->getTable($model::getName());
        $entry = $table->getById($model->getId(), $model::getIdField());
        if (!$entry) {
            $table->addEntry((new TestTableEntry(get_object_vars($model))));
            return true;
        }
        $entry->applyFromModel($model);
        return true;
    }

    /**
     * @param Query $query
     * @return QueryResult
     * @throws Exception
     */
    public function query(Query $query): QueryResult
    {
        $table = $this->getTable($query->modelClassName::getName());
        return $table->query($query);
    }
}