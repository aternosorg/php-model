<?php

namespace Aternos\Model\Driver\Test;

use Aternos\Model\ModelInterface;
use Aternos\Model\Query\DeleteQuery;
use Aternos\Model\Query\OrderField;
use Aternos\Model\Query\Query;
use Aternos\Model\Query\QueryResult;
use Aternos\Model\Query\SelectQuery;
use Aternos\Model\Query\UpdateQuery;
use Aternos\Model\Query\WhereGroup;

class TestTable
{
    /**
     * @var TestTableEntry[]
     */
    protected array $entries = [];

    /**
     * @param string $name
     */
    public function __construct(protected string $name)
    {
    }

    /**
     * @param array $entries
     * @return $this
     */
    public function addArrayEntries(array $entries): static
    {
        foreach ($entries as $entry) {
            $this->addEntry(new TestTableEntry($entry));
        }
        return $this;
    }

    /**
     * @param TestTableEntry ...$entry
     * @return $this
     */
    public function addEntry(TestTableEntry ...$entry): static
    {
        $this->entries = array_merge($this->entries, $entry);
        return $this;
    }

    /**
     * @param Query $query
     * @return QueryResult
     */
    public function query(Query $query): QueryResult
    {
        $entries = $this->findEntries($query->getWhere(), $query->getLimit()->start, $query->getLimit()->length);
        if ($order = $query->getOrder()) {
            $entries = $this->orderEntries($entries, $order);
        }

        $queryResult = new QueryResult(true);
        foreach ($entries as $entry) {
            if ($query instanceof SelectQuery) {
                // TODO: group
                /** @var class-string<ModelInterface> $modelClass */
                $modelClass = $query->modelClassName;
                $model = $modelClass::getModelFromData($entry->getDataForFields($query->getFields()));
                $queryResult->add($model);
            }
            if ($query instanceof UpdateQuery) {
                $entry->update($query->getFields());
            }
            if ($query instanceof DeleteQuery) {
                foreach ($this->entries as $key => $tableEntry) {
                    if ($tableEntry === $entry) {
                        unset($this->entries[$key]);
                        break;
                    }
                }
            }
        }
        if ($query instanceof UpdateQuery || $query instanceof DeleteQuery) {
            $queryResult->setAffectedRows(count($entries));
        }
    }

    /**
     * @param WhereGroup $where
     * @param int $offset
     * @param int|null $limit
     * @return TestTableEntry[]
     */
    protected function findEntries(WhereGroup $where, int $offset = 0, ?int $limit = null): array
    {
        $entries = [];
        foreach ($this->entries as $entry) {
            if (!$entry->matchesWhereGroup($where)) {
                continue;
            }
            if ($offset > 0) {
                $offset--;
                continue;
            }
            $entries[] = $entry;
            if ($limit !== null && count($entries) >= $limit) {
                break;
            }
        }
        return $entries;
    }

    /**
     * @param TestTableEntry $a
     * @param TestTableEntry $b
     * @param array $order
     * @return int
     */
    protected function compareEntries(TestTableEntry $a, TestTableEntry $b, array $order): int
    {
        foreach ($order as $column => $direction) {
            $aValue = $a->{$column};
            $bValue = $b->{$column};
            if ($aValue === $bValue) {
                continue;
            }
            if ($direction === OrderField::ASCENDING) {
                return $aValue > $bValue ? 1 : -1;
            } else {
                return $aValue < $bValue ? 1 : -1;
            }
        }
        return 0;
    }

    /**
     * @param array $entries
     * @param array $order
     * @return TestTableEntry[]
     */
    protected function orderEntries(array $entries, array $order): array
    {
        usort($entries, function (TestTableEntry $a, TestTableEntry $b) use ($order) {
            return $this->compareEntries($a, $b, $order);
        });
        return $entries;
    }
}