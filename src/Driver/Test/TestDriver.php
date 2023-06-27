<?php

namespace Aternos\Model\Driver\Test;

use Aternos\Model\Driver\Driver;
use Aternos\Model\Driver\Features\CRUDAbleInterface;
use Aternos\Model\Driver\Features\CRUDQueryableInterface;
use Aternos\Model\ModelInterface;
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
     * @throws Exception
     */
    public function getTable(string $name): TestTable
    {
        if (!$this->tables[$name]) {
            throw new Exception("Table " . $name . " does not exist.");
        }
        return $this->tables[$name];
    }

    /**
     * @param ModelInterface $model
     * @return TestTableEntry|null
     * @throws Exception
     */
    protected function getEntryFromModel(ModelInterface $model): ?TestTableEntry
    {
        $table = $this->getTable($model::getName());

    }

    /**
     * @param string $tableName
     * @param array $entry
     * @return $this
     */
    public function addEntry(string $tableName, array $entry): static
    {
        if (!isset($this->tables[$tableName])) {
            $this->tables[$tableName] = new TestTable($tableName);
        }
        $this->tables[$tableName]->addEntry(new TestTableEntry($entry));
        return $this;
    }

    public function delete(ModelInterface $model): bool
    {
        // TODO: Implement delete() method.
    }

    public function get(string $modelClass, mixed $id, ?ModelInterface $model = null): ?ModelInterface
    {
        // TODO: Implement get() method.
    }

    public function save(ModelInterface $model): bool
    {
        // TODO: Implement save() method.
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